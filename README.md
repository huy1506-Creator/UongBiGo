# 🍱 UongbiGo - Hệ thống đặt món Canteen

## 📌 Giới thiệu

**UongbiGo** là hệ thống đặt món Canteen được xây dựng nhằm mô phỏng quy trình đặt món trực tuyến trong khu vực trường học/canteen.

Hệ thống cho phép người dùng xem thực đơn, thêm món vào giỏ hàng, đặt món và theo dõi trạng thái đơn hàng.

Ngoài giao diện dành cho người dùng, hệ thống còn có giao diện dành cho **nhân viên bếp** và **quản trị viên (Admin)**.

> **Trạng thái dự án:** Frontend Prototype / Mock API  
> Phiên bản hiện tại chạy phía client và sử dụng `localStorage` để mô phỏng việc lưu trữ dữ liệu.

---

# 🎯 Mục tiêu của dự án

Dự án được xây dựng nhằm:

- Thực hành phát triển giao diện web.
- Thực hành lập trình JavaScript.
- Xây dựng quy trình đăng nhập và đăng ký.
- Thực hành phân quyền người dùng.
- Xây dựng chức năng giỏ hàng.
- Xây dựng quy trình đặt món và quản lý đơn hàng.
- Thực hành quản lý dữ liệu phía client.
- Làm quen với Git và GitHub.
- Xây dựng một project có thể sử dụng làm sản phẩm học tập và portfolio.

---

# 🚀 Các chức năng chính

## 👤 1. Người dùng

Người dùng có thể:

- Đăng ký tài khoản.
- Đăng nhập.
- Đăng xuất.
- Xem danh sách món ăn.
- Tìm kiếm và xem thực đơn.
- Thêm món vào giỏ hàng.
- Thay đổi số lượng món.
- Xóa món khỏi giỏ hàng.
- Xem tổng tiền.
- Đặt món.
- Theo dõi trạng thái đơn hàng.
- Xem chi tiết đơn hàng.
- Thực hiện quy trình thanh toán mô phỏng.

---

# 👨‍🍳 2. Nhân viên bếp - KDS

Hệ thống có giao diện **Kitchen Display System (KDS)** dành cho nhân viên bếp.

Nhân viên có thể:

- Xem các đơn hàng mới.
- Xem chi tiết đơn hàng.
- Theo dõi các món trong đơn.
- Cập nhật trạng thái đơn hàng.
- Theo dõi các đơn đang chờ xử lý.
- Quản lý tình trạng món ăn.
- Cập nhật trạng thái món còn/hết.

---

# 👨‍💼 3. Quản trị viên - Admin

Admin có thể:

- Xem Dashboard.
- Theo dõi thống kê.
- Quản lý đơn hàng.
- Quản lý thực đơn.
- Thêm món ăn.
- Chỉnh sửa món ăn.
- Ẩn món ăn.
- Xóa món ăn.
- Theo dõi trạng thái đơn hàng.
- Quản lý dữ liệu hệ thống.

---

# 🔐 Phân quyền người dùng

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
