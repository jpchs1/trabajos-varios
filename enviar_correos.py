#!/usr/bin/env python3
"""
Envío automático de los correos del reclamo Puerto Columbo desde tu Gmail.

⚠️  EJECÚTALO EN TU PROPIO COMPUTADOR (no en un servidor ajeno): usa tu cuenta de Gmail.

Requisitos (una sola vez):
  1) Activa la verificación en 2 pasos en tu cuenta Google.
  2) Crea una "Contraseña de aplicación" (App Password) en:
     https://myaccount.google.com/apppasswords  -> te da 16 caracteres.
     (Gmail ya NO permite enviar por SMTP con tu contraseña normal.)

Uso:
  export GMAIL_USER="jpchs1@gmail.com"
  export GMAIL_APP_PASSWORD="xxxxxxxxxxxxxxxx"   # los 16 caracteres, sin espacios
  python3 enviar_correos.py --dry-run     # muestra qué se enviaría, NO envía
  python3 enviar_correos.py               # envía de verdad (pide confirmación)
  python3 enviar_correos.py --only 1,4,15 # envía solo esos números

Antes de enviar: edita correos-envio-reclamo.md y reemplaza [teléfono] y [RUT] en la firma.
"""
import os, re, sys, ssl, smtplib, argparse, pathlib
from email.mime.text import MIMEText
from email.utils import formataddr

SRC = pathlib.Path(__file__).parent / "correos-envio-reclamo.md"
GLOBAL_CC = "fchaparro@gmail.com"
SENDER_NAME = "Juan Pablo Chaparro Soumastre"

def parse_emails():
    text = SRC.read_text(encoding="utf-8")
    emails = []
    for b in re.split(r"\n---\n", text):
        m = re.search(r"^###\s*(\d+)\)\s*(.+)$", b, re.MULTILINE)
        su = re.search(r"\*\*Asunto:\*\*\s*(.+)", b)
        to = re.search(r"\*\*Para:\*\*\s*(.+)", b)
        cc = re.search(r"\*\*CC:\*\*\s*(.+)", b)
        if not (m and su and to):
            continue
        cc_parts = [GLOBAL_CC]
        if cc:
            for c in cc.group(1).split(","):
                c = c.strip()
                if c and c.lower() != GLOBAL_CC.lower():
                    cc_parts.append(c)
        body = b[su.end():].lstrip("\n").strip("\n")
        emails.append(dict(num=int(m.group(1)), title=m.group(2).strip(),
                           to=to.group(1).strip(), cc=", ".join(cc_parts),
                           subject=su.group(1).strip(), body=body))
    return sorted(emails, key=lambda e: e["num"])

def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--dry-run", action="store_true", help="No envía; solo muestra.")
    ap.add_argument("--only", help="Lista de números, ej: 1,4,15")
    args = ap.parse_args()

    emails = parse_emails()
    if args.only:
        want = {int(x) for x in args.only.split(",")}
        emails = [e for e in emails if e["num"] in want]

    user = os.environ.get("GMAIL_USER")
    pwd = os.environ.get("GMAIL_APP_PASSWORD")

    print(f"Se procesarán {len(emails)} correos. CC global: {GLOBAL_CC}\n")
    for e in emails:
        warn = "  ⚠️ revisa [teléfono]/[RUT]" if "[completar]" in e["body"] else ""
        print(f"  #{e['num']:>2}  -> {e['to']:<34} cc={e['cc']}{warn}")

    if args.dry_run:
        print("\n[DRY-RUN] No se envió nada.")
        return
    if not user or not pwd:
        sys.exit("\nFalta GMAIL_USER y/o GMAIL_APP_PASSWORD. Define las variables de entorno (ver cabecera).")

    if input(f"\n¿Enviar {len(emails)} correos reales desde {user}? Escribe 'ENVIAR' para confirmar: ").strip() != "ENVIAR":
        sys.exit("Cancelado.")

    ctx = ssl.create_default_context()
    with smtplib.SMTP("smtp.gmail.com", 587) as s:
        s.starttls(context=ctx)
        s.login(user, pwd)
        for e in emails:
            msg = MIMEText(e["body"], "plain", "utf-8")
            msg["From"] = formataddr((SENDER_NAME, user))
            msg["To"] = e["to"]
            msg["Cc"] = e["cc"]
            msg["Subject"] = e["subject"]
            rcpts = [x.strip() for x in (e["to"] + "," + e["cc"]).split(",") if x.strip()]
            s.sendmail(user, rcpts, msg.as_string())
            print(f"  ✅ enviado #{e['num']} -> {e['to']}")
    print("\nListo. Revisa tu carpeta de Enviados en Gmail.")

if __name__ == "__main__":
    main()
