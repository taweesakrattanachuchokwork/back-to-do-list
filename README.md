# การติดตั้งและใช้งานระบบ

## ลงโปรแกรม Xampp
ติดตั้ง Xampp เพื่อใช้งาน Apache และ MySQL

## ฐานข้อมูล MySQL
ใช้งานฐานข้อมูล MySQL สำหรับเก็บข้อมูลของระบบ

## อัปโหลดไฟล์ SQL
นำเข้าไฟล์ฐานข้อมูล `To_Do_list.sql` ลงใน MySQL

---

## การติดตั้ง Composer

### 1. เปิด Terminal แล้วดาวน์โหลด Composer ด้วยคำสั่งนี้:
```sh
curl -sS https://getcomposer.org/installer | php
```

### 2. ติดตั้ง Composer ทั่วไป (global installation) โดยการย้ายไฟล์ที่ดาวน์โหลดไปไว้ใน directory ที่สามารถเรียกใช้งานได้จากทุกที่:
```sh
mv composer.phar /usr/local/bin/composer
```

### 3. ตรวจสอบการติดตั้ง Composer ด้วยคำสั่ง:
```sh
composer --version
```

---

## API ต่างๆ

### API Task Management
เป็น API สำหรับจัดการ Task เช่น ดึงข้อมูล เพิ่ม ลบ และแก้ไข
[เอกสาร API](https://documenter.getpostman.com/view/42623461/2sAYdfpW8H)

### API Task_and_detail Management
เป็น API สำหรับจัดการ Comments เช่น ดึงข้อมูล เพิ่ม ลบ และแก้ไข
[เอกสาร API](https://documenter.getpostman.com/view/42623461/2sAYdfpWS2)

### API User Management
เป็น API สำหรับจัดการสมาชิก เช่น ดึงข้อมูล เพิ่ม ลบ และแก้ไข
[เอกสาร API](https://documenter.getpostman.com/view/42623461/2sAYdfpWWM)

### JWT Authentication
API สำหรับการออก Token และเข้ารหัสผ่านครั้งแรก
[เอกสาร API](https://documenter.getpostman.com/view/42623461/2sAYdfpWWU)

