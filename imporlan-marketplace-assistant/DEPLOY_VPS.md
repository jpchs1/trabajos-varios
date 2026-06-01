# Montar el asistente ONLINE en un VPS

Esta guía deja el buscador corriendo **en un servidor** (no en tu PC), con una
página web que se abre **desde el celular** y una **búsqueda automática** cada
cierto tiempo.

> ⚠️ **Importante — qué hosting sirve**
>
> Esto necesita abrir un navegador Chrome de verdad en el servidor. Por eso:
> - ❌ **NO sirve un hosting compartido / cPanel** (los planes tipo
>   "Corporate / Deluxe / Unlimited SSD" para páginas web). No dejan correr un
>   navegador ni procesos largos, y va contra sus reglas de uso.
> - ✅ Sirve un **VPS / Cloud Server** (una máquina Linux con acceso `root`/SSH),
>   con al menos **2 GB de RAM**.
>
> ⚠️ **Riesgo:** al correr desde un servidor (IP de datacenter), Facebook puede
> pedir verificaciones o **suspender la cuenta**. Reducís el riesgo usando un
> *proxy residencial* y sin abusar de la frecuencia. Es bajo tu responsabilidad.

---

## 1. Requisitos del VPS

- Ubuntu 22.04 (o similar) con acceso `root` por SSH.
- 2 GB de RAM o más (Chromium necesita memoria).
- Python 3.10+.

## 2. Instalar todo

```bash
# Conectado por SSH al VPS:
apt update && apt install -y python3 python3-pip python3-venv git \
    xvfb x11vnc novnc websockify

# Traer el proyecto
git clone https://github.com/jpchs1/trabajos-varios.git
cd trabajos-varios/imporlan-marketplace-assistant

# Entorno e instalación
python3 -m venv .venv
. .venv/bin/activate
pip install -r requirements.txt
python -m playwright install --with-deps chromium
```

## 3. Iniciar sesión en Facebook (una sola vez)

El servidor no tiene pantalla, así que usamos un "monitor virtual" (Xvfb) y lo
miramos desde el navegador con noVNC.

```bash
export IMPORLAN_PROFILE_DIR=/root/.imporlan_marketplace_profile

# Monitor virtual + acceso web
Xvfb :99 -screen 0 1280x800x24 &
export DISPLAY=:99
x11vnc -display :99 -nopw -forever -bg
websockify --web=/usr/share/novnc 6080 localhost:5900 &

# Abrir Facebook para loguearte
python -m app.fb_login
```

Ahora, desde tu PC o celular, abrí en el navegador:

```
http://IP-DE-TU-VPS:6080/vnc.html
```

Vas a ver la ventana de Chrome. **Iniciá sesión en Facebook ahí** (usuario,
contraseña y la verificación que pida). Cuando ya estés dentro, volvé al SSH y
cortá `app.fb_login` con **Ctrl+C**. La sesión queda guardada en el perfil.

## 4. Levantar la web

```bash
export IMPORLAN_PROFILE_DIR=/root/.imporlan_marketplace_profile
export IMPORLAN_HEADLESS=1
export IMPORLAN_PORT=8000
python -m app.web_app
```

Entrá desde el celular a `http://IP-DE-TU-VPS:8000`. Ahí configurás keywords,
ciudades, radio y cada cuánto busca sola; y ves los resultados con foto.

## 5. Dejarlo prendido siempre (servicio systemd)

Creá `/etc/systemd/system/imporlan-web.service`:

```ini
[Unit]
Description=Imporlan Marketplace Web
After=network.target

[Service]
WorkingDirectory=/root/trabajos-varios/imporlan-marketplace-assistant
Environment=IMPORLAN_PROFILE_DIR=/root/.imporlan_marketplace_profile
Environment=IMPORLAN_HEADLESS=1
Environment=IMPORLAN_PORT=8000
ExecStart=/root/trabajos-varios/imporlan-marketplace-assistant/.venv/bin/python -m app.web_app
Restart=always

[Install]
WantedBy=multi-user.target
```

```bash
systemctl enable --now imporlan-web
systemctl status imporlan-web   # ver que esté "active (running)"
```

## 6. (Recomendado) Dominio + HTTPS

Para entrar con un nombre lindo y candado, poné un Nginx adelante apuntando al
puerto 8000 y un certificado gratis con `certbot`. (Opcional; se puede agregar
después.)

---

## Notas de seguridad

- El perfil de Facebook (cookies) queda en el VPS: tratá el servidor con
  cuidado (contraseña fuerte, firewall).
- Si Facebook empieza a pedir verificaciones seguido, bajá la frecuencia de la
  búsqueda automática (más minutos entre búsquedas) y considerá un proxy
  residencial.
