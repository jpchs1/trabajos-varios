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

Adjuntos: deja los archivos a adjuntar (comprobante de pago, factura, etc.) dentro de una
carpeta llamada "adjuntos/" junto a este script. Se adjuntarán automáticamente a TODOS los correos.
Ejemplo:
  adjuntos/comprobante_pago.png
  adjuntos/factura_551879.pdf
"""
import os, re, sys, ssl, smtplib, argparse, pathlib, mimetypes
from email.mime.text import MIMEText
from email.mime.multipart import MIMEMultipart
from email.mime.base import MIMEBase
from email import encoders
from email.utils import formataddr

SRC = pathlib.Path(__file__).parent / "correos-envio-reclamo.md"
ADJUNTOS_DIR = pathlib.Path(__file__).parent / "adjuntos"   # deja aquí el comprobante, la factura, etc.
GLOBAL_CC = "fchaparro@gmail.com"
SENDER_NAME = "Juan Pablo Chaparro Soumastre"

def cargar_adjuntos():
    """Devuelve la lista de archivos a adjuntar (todo lo que haya en ./adjuntos/)."""
    if not ADJUNTOS_DIR.is_dir():
        return []
    return sorted(p for p in ADJUNTOS_DIR.iterdir() if p.is_file() and not p.name.startswith("."))

def construir_mensaje(user, e, adjuntos):
    """MIMEText simple si no hay adjuntos; multipart con archivos si los hay."""
    if not adjuntos:
        msg = MIMEText(e["body"], "plain", "utf-8")
    else:
        msg = MIMEMultipart()
        msg.attach(MIMEText(e["body"], "plain", "utf-8"))
        for path in adjuntos:
            ctype, _ = mimetypes.guess_type(path.name)
            maintype, subtype = (ctype.split("/", 1) if ctype else ("application", "octet-stream"))
            part = MIMEBase(maintype, subtype)
            part.set_payload(path.read_bytes())
            encoders.encode_base64(part)
            part.add_header("Content-Disposition", "attachment", filename=path.name)
            msg.attach(part)
    msg["From"] = formataddr((SENDER_NAME, user))
    msg["To"] = e["to"]
    msg["Cc"] = e["cc"]
    msg["Subject"] = e["subject"]
    return msg

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
    ap.add_argument("--test", metavar="CORREO", help="Modo prueba: redirige TODOS los correos SOLO a CORREO (no envía a los destinatarios reales).")
    args = ap.parse_args()

    emails = parse_emails()
    if args.only:
        want = {int(x) for x in args.only.split(",")}
        emails = [e for e in emails if e["num"] in want]

    user = os.environ.get("GMAIL_USER")
    pwd = os.environ.get("GMAIL_APP_PASSWORD")

    adjuntos = cargar_adjuntos()
    print(f"Se procesarán {len(emails)} correos. CC global: {GLOBAL_CC}")
    if adjuntos:
        print("Adjuntos (se añaden a TODOS los correos):")
        for p in adjuntos:
            print(f"   📎 {p.name}")
    else:
        print(f"Sin adjuntos (carpeta {ADJUNTOS_DIR}/ vacía o inexistente).")
    if args.test:
        print(f"\n*** MODO PRUEBA *** Todos se enviarán SOLO a: {args.test} (NO a los destinatarios reales).")
    print()
    for e in emails:
        destino = args.test if args.test else e["to"]
        print(f"  #{e['num']:>2}  -> {destino:<34} cc={'' if args.test else e['cc']}")

    if args.dry_run:
        print("\n[DRY-RUN] No se envió nada.")
        return
    if not user or not pwd:
        sys.exit("\nFalta GMAIL_USER y/o GMAIL_APP_PASSWORD. Define las variables de entorno (ver cabecera).")

    destino_txt = f"SOLO a {args.test} (prueba)" if args.test else "a los destinatarios reales"
    if input(f"\n¿Enviar {len(emails)} correo(s) {destino_txt} desde {user}? Escribe 'ENVIAR' para confirmar: ").strip() != "ENVIAR":
        sys.exit("Cancelado.")

    ctx = ssl.create_default_context()
    with smtplib.SMTP("smtp.gmail.com", 587) as s:
        s.starttls(context=ctx)
        s.login(user, pwd)
        for e in emails:
            if args.test:
                e_eff = dict(e); e_eff["to"] = args.test; e_eff["cc"] = ""
                e_eff["subject"] = "[PRUEBA] " + e["subject"]
                rcpts = [args.test]
            else:
                e_eff = e
                rcpts = [x.strip() for x in (e["to"] + "," + e["cc"]).split(",") if x.strip()]
            msg = construir_mensaje(user, e_eff, adjuntos)
            s.sendmail(user, rcpts, msg.as_string())
            print(f"  ✅ enviado #{e['num']} -> {', '.join(rcpts)}")
    print("\nListo. Revisa tu carpeta de Enviados en Gmail.")

if __name__ == "__main__":
    main()
