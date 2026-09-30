# 🍱 UongbiGo - Hệ thống đặt món Canteen

## 📌 Giới thiệu

**UongbiGo** là hệ thống đặt món Canteen được xây dựng nhằm mô phỏng quy trình đặt món trực tuyến trong khu vực trường học/canteen.

Hệ thống cho phép người dùng xem thực đơn, tìm kiếm món ăn, thêm món vào giỏ hàng, đặt món và theo dõi trạng thái đơn hàng.

Ngoài giao diện dành cho người dùng, hệ thống còn có giao diện dành cho **nhân viên bếp** và **quản trị viên (Admin)**.

> **Trạng thái dự án:** Frontend Prototype / Mock API  
> Phiên bản hiện tại chạy phía client và sử dụng `localStorage` để mô phỏng việc lưu trữ dữ liệu.

---

## 🎯 Mục tiêu của dự án

Dự án được xây dựng nhằm:

- Thực hành phát triển giao diện website.
- Thực hành lập trình JavaScript.
- Xây dựng chức năng đăng ký và đăng nhập.
- Thực hành phân quyền người dùng.
- Xây dựng chức năng giỏ hàng.
- Xây dựng quy trình đặt món và quản lý đơn hàng.
- Thực hành lưu trữ và xử lý dữ liệu phía client.
- Làm quen với Git và GitHub.
- Xây dựng project phục vụ học tập và portfolio cá nhân.

---

## 🚀 Chức năng chính

### 👤 Người dùng

Người dùng có thể:

- Đăng ký tài khoản.
- Đăng nhập và đăng xuất.
- Xem danh sách món ăn.
- Tìm kiếm món ăn.
- Xem thông tin món ăn.
- Thêm món vào giỏ hàng.
- Tăng/giảm số lượng món.
- Xóa món khỏi giỏ hàng.
- Xem tổng tiền.
- Đặt món.
- Theo dõi trạng thái đơn hàng.
- Xem chi tiết đơn hàng.
- Thực hiện thanh toán mô phỏng.

---

### 👨‍🍳 Nhân viên bếp - KDS

Hệ thống cung cấp giao diện **Kitchen Display System (KDS)** cho nhân viên bếp.

Nhân viên có thể:

- Xem danh sách đơn hàng.
- Xem chi tiết đơn hàng.
- Theo dõi đơn hàng đang chờ xử lý.
- Cập nhật trạng thái đơn hàng.
- Theo dõi tình trạng món ăn.
- Cập nhật trạng thái món còn/hết.

---

### 👨‍💼 Quản trị viên - Admin

Admin có thể:

- Xem Dashboard.
- Theo dõi thống kê cơ bản.
- Quản lý thực đơn.
- Thêm món ăn.
- Chỉnh sửa món ăn.
- Ẩn món ăn.
- Xóa món ăn.
- Quản lý đơn hàng.
- Theo dõi trạng thái đơn hàng.

---

## 🔐 Phân quyền người dùng

Hệ thống hiện có 3 vai trò:

| Vai trò | Chức năng |
|---|---|
| `ROLE_BUYER` | Người dùng/khách hàng đặt món |
| `ROLE_STAFF` | Nhân viên bếp quản lý đơn |
| `ROLE_ADMIN` | Quản trị viên quản lý hệ thống |

Sau khi đăng nhập, hệ thống kiểm tra vai trò của người dùng và chuyển đến giao diện phù hợp.

```text
ROLE_BUYER
    ↓
Giao diện người dùng

ROLE_STAFF
    ↓
Giao diện KDS

ROLE_ADMIN
    ↓
Admin Dashboard
