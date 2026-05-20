# ============================================================
# Dockerfile - Personal Resume Web + Admin Panel
# ============================================================
# Stack: PHP 8.2 + Apache (รวมในตัว — ง่าย)
# - Public:  http://localhost:8090/         → index.html (Resume)
# - Admin:   http://localhost:8090/admin/   → PHP Admin Panel
# Data:      /var/www/html/resume-data.json (mount เป็น volume เพื่อเก็บข้อมูลถาวร)
# ============================================================

FROM php:8.2-apache

LABEL maintainer="Patiwat Meekaeo <patiwatmeekaeo@gmail.com>"
LABEL description="Personal Resume Web with Admin Panel - 3 Languages (TH/EN/ZH)"
LABEL version="2.0"

# Enable rewrite (เผื่ออนาคต)
RUN a2enmod rewrite

# Copy files เข้า DocumentRoot
COPY index.html /var/www/html/
COPY resume-data.json /var/www/html/
COPY admin/ /var/www/html/admin/

# ให้ Apache user (www-data) เขียนไฟล์ JSON ได้
RUN chown www-data:www-data /var/www/html/resume-data.json \
    && chmod 664 /var/www/html/resume-data.json

# สร้างโฟลเดอร์ certs/ สำหรับเก็บใบประกาศนียบัตร (jpg, png, pdf)
RUN mkdir -p /var/www/html/certs \
    && chown -R www-data:www-data /var/www/html/certs \
    && chmod 775 /var/www/html/certs

# เพิ่ม upload size limit ของ PHP เป็น 10MB (เผื่อ PDF ใบใหญ่)
RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 12M" >> /usr/local/etc/php/conf.d/uploads.ini

# ห้ามให้ใครเข้ามาดู _auth.php ตรง ๆ
RUN echo '<FilesMatch "^_">' > /etc/apache2/conf-enabled/deny-underscore.conf \
    && echo '    Require all denied' >> /etc/apache2/conf-enabled/deny-underscore.conf \
    && echo '</FilesMatch>' >> /etc/apache2/conf-enabled/deny-underscore.conf

# Default password (override ตอน docker run ด้วย -e ADMIN_PASSWORD=...)
ENV ADMIN_PASSWORD="admin123"

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -fs http://localhost/ || exit 1
