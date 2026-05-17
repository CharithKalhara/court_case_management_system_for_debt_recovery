# Docker & Docker Compose Setup Guide

This guide helps you install Docker and Docker Compose on a Linux VM (Ubuntu recommended).

---

## Requirements

- Ubuntu 20.04 / 22.04 VM
- sudo access
- Internet connection

---

## 1. Update System

```bash
sudo apt update && sudo apt upgrade -y
```

---

## 2. Install Docker

```bash
sudo apt install docker.io -y
```

---

## 3. Start Docker Service

```bash
sudo systemctl start docker
sudo systemctl enable docker
```

---

## 4. Add User Permission (IMPORTANT)

```bash
sudo usermod -aG docker $USER
```

After running this command:
- Logout from SSH / terminal
- Login again

---

## 5. Install Docker Compose

```bash
sudo apt install docker-compose
```

---

## 6. Check Docker Compose Version

```bash
docker compose version
```

---

## 7. Run Your Project

### Build and run:

```bash
docker compose up --build
```

### Run in background (recommended):

```bash
docker compose up --build -d
```

---

## 8. Stop Project

```bash
docker compose down
```

---

## 9. Most Important Command (Daily Use)

```bash
docker compose up --build -d
```

---

## Done 

Your Docker environment is now ready.