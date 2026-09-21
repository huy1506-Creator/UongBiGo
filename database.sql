CREATE DATABASE IF NOT EXISTS uongbigo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uongbigo;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS menu_items;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
  maNguoiDung INT AUTO_INCREMENT PRIMARY KEY,
  hoTen VARCHAR(150) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  matKhau CHAR(64) NOT NULL,
  vaiTro ENUM('ROLE_BUYER','ROLE_STAFF','ROLE_ADMIN') NOT NULL DEFAULT 'ROLE_BUYER'
) ENGINE=InnoDB;

CREATE TABLE menu_items (
  maMon INT AUTO_INCREMENT PRIMARY KEY,
  tenMon VARCHAR(200) NOT NULL,
  donGia DECIMAL(12,2) NOT NULL,
  moTa TEXT,
  hinhAnh TEXT,
  trangThai ENUM('CON_HANG','HET_HANG','DA_XOA') NOT NULL DEFAULT 'CON_HANG'
) ENGINE=InnoDB;

CREATE TABLE orders (
  maDonHang INT AUTO_INCREMENT PRIMARY KEY,
  maNguoiDung INT NOT NULL,
  maDonRutGon VARCHAR(30) NOT NULL,
  ngayDat DATETIME NOT NULL,
  thoiGianNhan DATETIME NULL,
  trangThai VARCHAR(40) NOT NULL,
  tongTien DECIMAL(12,2) NOT NULL DEFAULT 0,
  maQr VARCHAR(100) NULL,
  thoiHanThanhToan DATETIME NULL,
  CONSTRAINT fk_orders_user FOREIGN KEY (maNguoiDung) REFERENCES users(maNguoiDung)
) ENGINE=InnoDB;

CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  maDonHang INT NOT NULL,
  maMon INT NOT NULL,
  tenMon VARCHAR(200) NOT NULL,
  soLuong INT NOT NULL,
  donGia DECIMAL(12,2) NOT NULL,
  CONSTRAINT fk_items_order FOREIGN KEY (maDonHang) REFERENCES orders(maDonHang) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (hoTen,email,matKhau,vaiTro) VALUES
('Quan ly Canteen','admin@uongbigo.vn','e86f78a8a3caf0b60d8e74e5942aa6d86dc150cd3c03338aef25b7d2d7e3acc7','ROLE_ADMIN'),
('Nhan vien bep','bep@uongbigo.vn','671989e46cd483c5235eeb3e278f13139bcbc7d3d23dcac19a583190d4b04ee5','ROLE_STAFF'),
('Sinh vien Demo','sv@uongbigo.vn','471425fac023b25a0f81aaac21cc5cfc6ccb9203c732105f818ae2c84836ddf3','ROLE_BUYER');

INSERT INTO menu_items (tenMon,donGia,moTa,hinhAnh,trangThai) VALUES
('Com tam suon bi',32000,'Com tam suon nuong, bi, cha, trung op la','', 'CON_HANG'),
('Bun cha Ha Noi',30000,'Bun, cha nuong than hoa, nuoc mam chua ngot','', 'CON_HANG'),
('Pho bo tai',35000,'Pho bo truyen thong, nuoc dung ninh xuong','', 'CON_HANG'),
('Bun dau mam tom',28000,'Bun, dau ran, cha com, mam tom nguyen chat','', 'HET_HANG'),
('Mi xao hai san',33000,'Mi xao gion cung tom, muc, rau cu','', 'CON_HANG'),
('Salad uc ga',29000,'Uc ga ap chao, rau xa lach, sot me rang','', 'CON_HANG');
