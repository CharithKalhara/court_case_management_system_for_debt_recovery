# Docker & Docker Compose Setup Guide

This guide helps you install Docker and Docker Compose on a Linux VM (Ubuntu recommended).

---

## Requirements
- Ubuntu 20.04 / 22.04 VM
- sudo access
- Internet connection

---

## Install Docker

sudo apt update && sudo apt upgrade -y
sudo apt install docker.io -y

---

## Start Docker

sudo systemctl start docker
sudo systemctl enable docker

---

## Add User Permission (IMPORTANT)

sudo usermod -aG docker $USER

NOTE: logout and login again after this step

---

## Install Docker Compose (v2)

sudo apt update
sudo apt install docker-compose-plugin -y

---

## Check Docker Compose

docker compose version

---

## Run Project

docker compose up --build

Run in background:

docker compose up --build -d

---

## Stop Project

docker compose down

---

## Main Command

docker compose up --build -d

---

Done.