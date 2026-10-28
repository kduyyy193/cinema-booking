<div align="center">

# 🎬 CineBooking

**Website đặt vé xem phim trực tuyến**

![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)
![SQLite](https://img.shields.io/badge/SQLite-PDO-003B57?logo=sqlite&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5-7952B3?logo=bootstrap&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-Fetch_API-F7DF1E?logo=javascript&logoColor=black)

</div>

---

## 🎓 Thông tin học phần

**Project học phần Công nghệ Web (CT275)**
Học kỳ I, Năm học 2025-2026

| | |
|---|---|
| **Tên dự án** | CineBooking – Website đặt vé xem phim trực tuyến |
| **Nhóm** | 28 |
| **MSSV 1** | NA25V7X503 |
| **Họ tên SV 1** | Neáng Thi Run |
| **MSSV 2** | DC25V7X712 |
| **Họ tên SV 2** | Võ Văn Dương |
| **MSSV 3** | DC25V7X403 |
| **Họ tên SV 3** | Nguyễn Khánh Duy |
| **Lớp học phần** | CT125 |

## ✨ Giới thiệu

Khách xem phim, chọn suất chiếu và ghế, đặt vé rồi thanh toán bằng QR giả lập. Quản trị viên quản lý phim, rạp, suất chiếu, đơn vé và theo dõi doanh thu.

## 🚀 Tính năng

| 🍿 Khách hàng | 🛠️ Quản trị |
|---|---|
| Xem phim, tìm kiếm, lọc theo thể loại | Quản lý phim, thể loại, rạp, phòng, ghế |
| Xem lịch chiếu theo ngày, rạp | Quản lý suất chiếu, chống trùng giờ |
| Chọn ghế trực quan, tính tiền tức thì | Quản lý đơn vé và người dùng |
| Thanh toán QR và thẻ demo | Dashboard và báo cáo doanh thu |
| Xem lịch sử vé, hủy vé | Xuất báo cáo CSV |

## ⚙️ Chạy nhanh

Yêu cầu: **PHP 8.1+** với các extension `pdo_sqlite`, `sqlite3`, `mbstring`, `fileinfo`, **Composer** (nạp lớp PSR-4), và có Internet (giao diện nạp Bootstrap, QR, biểu đồ từ CDN). Chưa có PHP hoặc gặp lỗi thì xem [hướng dẫn từng bước cho Windows](#-hướng-dẫn-chạy-từng-bước-windows) hoặc [Linux/macOS](#-chạy-trên-linuxmacos) bên dưới.

```
composer install
php database/seed.php
php -S localhost:8000 -t public public/index.php
```

Mở **http://localhost:8000**

## 📝 Ghi chú vận hành

- Tài khoản demo sau khi seed: `admin@cinema.test / admin123`, `customer@cinema.test / customer123`, `customer2@cinema.test / customer123`.
- Cấu hình ở `app/config/config.php`. Trước khi đưa lên môi trường thật cần đổi `webhook_secret`.
- Thẻ test: `4242 4242 4242 4242` thanh toán thành công, `4000 0000 0000 0002` bị từ chối (hạn trong tương lai, CVV 3 số).
- Thanh toán gửi webhook có chữ ký về `/webhooks/mock-payment`. Máy chủ `php -S` chạy một tiến trình (luôn như vậy trên Windows) không tự gọi lại chính nó được, nên khi đó ứng dụng xử lý sự kiện trực tiếp trong tiến trình với cùng một đoạn mã. Trên Linux/macOS đặt `PHP_CLI_SERVER_WORKERS=4` để webhook đi qua HTTP thật.
- Đơn chờ thanh toán quá hạn được giải phóng ghế mỗi lần mở trang chọn ghế, thanh toán, vé của tôi và trang admin. Giao dịch kẹt ở `processing` quá 5 phút sau hạn giữ ghế bị chuyển `failed` để ghế không bị giữ mãi.
- Mã nguồn dùng namespace `App\` theo chuẩn PSR-4, nạp bằng Composer (`composer.json`). Lớp mới đặt đúng thư mục: `App\Models\Movie` nằm ở `app/Models/Movie.php`.
- Trạng thái thanh toán cập nhật bằng SSE (`/payment/stream`) khi máy chủ xử lý được nhiều yêu cầu cùng lúc (nginx + PHP-FPM, hoặc `PHP_CLI_SERVER_WORKERS=4` trên Linux/macOS). `php -S` một tiến trình (Windows) tự chuyển sang truy vấn định kỳ để không bị treo; ép chế độ bằng biến môi trường `CINEBOOKING_WATCH_MODE=sse` hoặc `poll`.
- HTTP caching: trang công khai gửi `ETag` và `Cache-Control: private, no-cache` (trả `304` khi chưa đổi), trang cá nhân và quản trị gửi `no-store`; poster và tệp trong `/assets` có `ETag`, `Last-Modified` và `Cache-Control: public, max-age=604800`. Cấu hình nginx trong `deploy/nginx/cinebooking.conf` phục vụ các tệp tĩnh này trực tiếp và tắt đệm cho SSE.

## 🚀 Hướng dẫn chạy từng bước (Windows)

**Bước 1. Cài PHP 8.1 trở lên.** Mở PowerShell:

```powershell
winget install PHP.PHP.8.4
```

Đóng PowerShell và mở lại để nhận `PATH`, rồi kiểm tra:

```powershell
php -v
```

**Bước 2. Bật các extension cần thiết** (`pdo_sqlite`, `sqlite3`, `mbstring`, `fileinfo`). Bản cài bằng winget chưa có `php.ini`, nên tạo như sau:

```powershell
$dir = Split-Path (Get-Command php).Source
Copy-Item "$dir\php.ini-development" "$dir\php.ini"
(Get-Content "$dir\php.ini") `
  -replace '^;\s*extension_dir = "ext"','extension_dir = "ext"' `
  -replace '^;\s*extension=(pdo_sqlite|sqlite3|mbstring|fileinfo|curl|openssl)\s*$','extension=$1' |
  Set-Content "$dir\php.ini"
php -m | Select-String "sqlite|mbstring|fileinfo"
```

Kết quả phải có đủ 4 dòng: `fileinfo`, `mbstring`, `pdo_sqlite`, `sqlite3`. Nếu báo lỗi `could not find driver` khi chạy seed, nghĩa là bước này chưa đạt. Dùng PHP cài kiểu khác (XAMPP, tải zip từ windows.php.net) thì sửa `php.ini` tương ứng theo cách tương tự.

**Bước 3. Cài Composer (https://getcomposer.org/download/), vào thư mục project, cài autoload và tạo database mẫu:**

```powershell
cd đường\dẫn\tới\cinema-booking
composer install
php database/seed.php
```

Thấy dòng `OK. Database: ...` là thành công. Có thể chạy lại lệnh này bất cứ lúc nào để đưa dữ liệu về trạng thái ban đầu. **Lưu ý:** mọi dữ liệu đã nhập (đơn vé, phim thêm mới, tài khoản đăng ký) sẽ bị xóa.

**Bước 4. Chạy server:**

```powershell
php -S localhost:8000 -t public public/index.php
```

**Bước 5. Mở trình duyệt:** http://localhost:8000

| Vai trò | Email | Mật khẩu |
|---|---|---|
| Quản trị | `admin@cinema.test` | `admin123` |
| Khách | `customer@cinema.test` | `customer123` |
| Khách thứ hai | `customer2@cinema.test` | `customer123` |

**Thử nhanh luồng đặt vé:** đăng nhập khách, vào Lịch chiếu, chọn suất, chọn ghế, Tiếp tục, Xác nhận, rồi thanh toán bằng tab Thẻ với số `4242 4242 4242 4242` (hạn bất kỳ trong tương lai, CVV 3 số). Số `4000 0000 0000 0002` sẽ bị từ chối để thử lại.

## 🐧 Chạy trên Linux/macOS

```bash
# Ubuntu/Debian
sudo apt install php-cli php-sqlite3 php-mbstring
# macOS (Homebrew)
brew install php composer

php -v                       # cần 8.1 trở lên
php -m | grep -Ei "sqlite|mbstring|fileinfo"

cd /đường/dẫn/tới/cinema-booking
composer install
php database/seed.php
PHP_CLI_SERVER_WORKERS=4 php -S localhost:8000 -t public public/index.php
```

`PHP_CLI_SERVER_WORKERS=4` cho phép server xử lý nhiều request song song, nhờ đó webhook thanh toán đi qua HTTP thật. Bỏ biến này đi vẫn chạy được. Nếu `public/uploads` hoặc `storage` không ghi được, chạy `chmod -R u+w public/uploads storage`.

## 🧪 Dùng PHP cài kiểu khác trên Windows (XAMPP, file zip)

- **XAMPP:** dùng `C:\xampp\php\php.exe` thay cho `php`, hoặc thêm `C:\xampp\php` vào `PATH`. Mở `C:\xampp\php\php.ini`, bỏ dấu `;` đầu các dòng `extension=pdo_sqlite`, `extension=sqlite3`, `extension=mbstring`, `extension=fileinfo`.
- **File zip từ windows.php.net:** giải nén, đổi `php.ini-development` thành `php.ini`, bỏ `;` ở `extension_dir = "ext"` và 4 extension trên, rồi thêm thư mục PHP vào `PATH`.

Sau khi sửa, kiểm tra bằng `php -m` rồi làm tiếp từ Bước 3.

## ❗ Lỗi thường gặp

| Hiện tượng | Cách xử lý |
|---|---|
| `php` không được nhận ra | Đóng và mở lại PowerShell sau khi cài, hoặc kiểm tra PHP đã nằm trong `PATH` |
| `could not find driver` | Chưa bật `pdo_sqlite`, làm lại Bước 2 |
| `Database chưa được tạo` | Chạy `php database/seed.php` (Bước 3) |
| `Failed opening required ... vendor/autoload.php` | Chưa chạy `composer install` (Bước 3) |
| Cổng 8000 đang bận | Đổi cổng, ví dụ `php -S localhost:8080 -t public public/index.php` |
| Không tải được giao diện hoặc mã QR | Máy cần có Internet vì Bootstrap, biểu tượng, QR và biểu đồ nạp từ CDN |
| Thanh toán thẻ xong mà trang không đổi | Tải lại trang. Trên Windows server chạy một tiến trình, ứng dụng tự xử lý webhook nội bộ nên kết quả có ngay sau khi gửi form |
