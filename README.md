# Personal Resume Web v2 — Patiwat Meekaeo

เว็บ Resume ส่วนตัวรองรับ 3 ภาษา (ไทย / EN / 中文) **+ Admin Panel** แก้ไขข้อมูลผ่านเว็บได้เลย
รันด้วย Docker (PHP 8.2 + Apache)

---

## โครงสร้างไฟล์

```
resume-web/
├── index.html              # หน้า Resume (Public)
├── resume-data.json        # ข้อมูล Resume ทั้งหมด (แก้ผ่าน Admin)
├── admin/
│   ├── index.php           # Admin Panel หลัก (CRUD ทุกหมวด)
│   ├── login.php           # หน้า Login
│   ├── logout.php
│   ├── api.php             # Endpoint บันทึก/ลบ JSON
│   └── _auth.php           # Helper (deny ผ่าน Apache)
├── Dockerfile
└── README.md
```

---

## วิธีรัน (เริ่มต้นใหม่)

### 0. ถ้ามี container เดิมอยู่ ให้ลบก่อน

```cmd
docker stop resume && docker rm resume
docker rmi patiwat-resume
```

### 1. Build Image ใหม่

```cmd
cd C:\Users\MECPatiwatM\resume-web
docker build -t patiwat-resume .
```

> ครั้งนี้จะดาวน์โหลด `php:8.2-apache` (~110MB) — ใช้เวลา 1–2 นาที

### 2. Run Container

**แบบเบสิก (password เริ่มต้นคือ `admin123`):**

```cmd
docker run -d -p 8090:80 --name resume patiwat-resume
```

**แบบกำหนด password เอง (แนะนำ!):**

```cmd
docker run -d -p 8090:80 --name resume -e ADMIN_PASSWORD=YourSecretPassword123 patiwat-resume
```

**แบบเก็บข้อมูลถาวร (ข้อมูลไม่หายเวลา rebuild):**

```cmd
docker run -d -p 8090:80 --name resume ^
  -e ADMIN_PASSWORD=YourSecretPassword123 ^
  -v %cd%\resume-data.json:/var/www/html/resume-data.json ^
  patiwat-resume
```

> `^` คือเครื่องหมายต่อบรรทัดใน CMD (เหมือน `\` ใน Linux)

### 3. เข้าใช้งาน

- **หน้า Resume** → http://localhost:8090
- **Admin Panel** → http://localhost:8090/admin/
- Password (default): `admin123`

---

## Admin Panel ทำอะไรได้

✅ **ข้อมูลส่วนตัว** — แก้ชื่อ, ตำแหน่ง, email, เบอร์, สรุป (ทั้ง 3 ภาษา)
✅ **ประสบการณ์ทำงาน** — เพิ่ม/แก้/ลบ + เลื่อนลำดับขึ้นลง
✅ **การศึกษา** — เพิ่ม/แก้/ลบ
✅ **ทักษะ** — จัดกลุ่ม + เพิ่ม tags
✅ **ใบรับรอง / อบรม** — เพิ่ม/แก้/ลบ

ทุกหมวดมี **Tab สลับภาษา** ใน Modal เดียวกัน — กรอกครั้งเดียวครบ 3 ภาษา

---

## คำสั่ง Docker ที่ใช้บ่อย

```cmd
docker ps                    :: ดู container ที่รันอยู่
docker logs resume           :: ดู log
docker stop resume           :: หยุด
docker start resume          :: เปิดใหม่
docker restart resume        :: รีสตาร์ท
docker rm -f resume          :: ลบ container
docker exec -it resume bash  :: เข้าไปใน container
```

---

## ข้อควรระวัง / Tips

### 1. รหัสผ่าน
- ค่าเริ่มต้นคือ `admin123` — **เปลี่ยนทันทีก่อนใช้จริง!**
- กำหนดผ่าน `-e ADMIN_PASSWORD=...` ตอน `docker run`

### 2. ข้อมูลหายเวลา rebuild
- ถ้าไม่ใช้ `-v` mount volume → ข้อมูลใน container จะหายเมื่อลบ container
- **แนะนำใช้ -v เสมอ** (ดูคำสั่งข้อ 2 ด้านบน)

### 3. ดู `resume-data.json` ปัจจุบัน
```cmd
docker exec resume cat /var/www/html/resume-data.json
```

### 4. Backup ข้อมูล
```cmd
docker cp resume:/var/www/html/resume-data.json .\backup.json
```

### 5. Restore ข้อมูล
```cmd
docker cp .\backup.json resume:/var/www/html/resume-data.json
docker restart resume
```

---

## Deploy ขึ้น Production

หลังจากทดสอบบนเครื่องได้แล้ว สามารถ deploy ได้:

### Option A: Cloud VPS + Docker
```bash
docker run -d -p 80:80 --restart unless-stopped \
  -e ADMIN_PASSWORD=YourSecretPassword \
  -v /opt/resume/resume-data.json:/var/www/html/resume-data.json \
  --name resume patiwat-resume
```

### Option B: Push to Docker Hub
```bash
docker tag patiwat-resume:latest yourusername/patiwat-resume:latest
docker push yourusername/patiwat-resume:latest
```

### ⚠️ Production Checklist
- [ ] เปลี่ยน `ADMIN_PASSWORD` เป็นรหัสที่ปลอดภัย
- [ ] ใช้ HTTPS (Nginx reverse proxy + Let's Encrypt)
- [ ] Backup `resume-data.json` เป็นประจำ
- [ ] ปิด PHP error display ใน production

---

## License

© 2026 Patiwat Meekaeo
