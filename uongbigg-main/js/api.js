/* =====================================================================
   Uong Bi GO - API helper (PHIEN BAN FRONTEND-ONLY / MOCK)
   -----------------------------------------------------------------------
   File nay thay the hoan toan cho backend that. Khong co request mang nao
   duoc goi di ca - moi "API" (dang nhap, thuc don, gio hang, don hang,
   thanh toan QR, KDS, quan tri...) deu duoc gia lap va luu trong
   localStorage cua trinh duyet, dong vai tro nhu mot co so du lieu nho.
   Tat ca cac trang HTML/JS khac (index.html, menu.html, cart.html,
   orders.html, kds.html, admin/*.html, js/cart.js) khong can sua doi gi
   vi chung chi goi qua cac ham dung chung: Auth, apiFetch, goTo,
   formatCurrency, formatDateTime, statusPillHtml... y het ban goc.
   ===================================================================== */

/* ---------- "Dia chi API" chi con mang tinh trang tri, khong con dung ---------- */
const API_BASE = "(mock-local)";

/* ---------- Duong dan goc cua site (hoat dong ca khi host trong thu muc con) ---------- */
const SITE_ROOT = new URL("../", document.currentScript.src).href;
function goTo(page) {
  window.location.href = SITE_ROOT + String(page).replace(/^\/+/, "");
}

/* ===================== Auth (luu phien dang nhap trong localStorage) ===================== */
const Auth = {
  getToken() {
    try { return localStorage.getItem("ubg_token"); } catch (e) { return null; }
  },
  getUser() {
    try { return JSON.parse(localStorage.getItem("ubg_user") || "null"); } catch (e) { return null; }
  },
  setSession(token, user) {
    try {
      localStorage.setItem("ubg_token", token);
      localStorage.setItem("ubg_user", JSON.stringify(user));
    } catch (e) { /* trinh duyet chan luu tru - bo qua */ }
  },
  clear() {
    try {
      localStorage.removeItem("ubg_token");
      localStorage.removeItem("ubg_user");
    } catch (e) { /* noop */ }
  },
  isLoggedIn() { return !!this.getToken(); },
  requireRole(allowedRoles) {
    const user = this.getUser();
    if (!this.isLoggedIn() || !user) {
      goTo("index.html");
      return null;
    }
    if (allowedRoles && !allowedRoles.includes(user.vaiTro)) {
      alert("Tai khoan cua ban khong co quyen truy cap trang nay.");
      redirectByRole(user.vaiTro);
      return null;
    }
    return user;
  },
};

function redirectByRole(vaiTro) {
  const map = { ROLE_BUYER: "menu.html", ROLE_STAFF: "kds.html", ROLE_ADMIN: "admin/dashboard.html" };
  goTo(map[vaiTro] || "index.html");
}

/* ===================== "Co so du lieu" mock, luu trong localStorage ===================== */
const DB_KEY = "ubg_mock_db_v1";

function seedDB() {
  const db = {
    seq: { user: 3, mon: 6, don: 0 },
    users: [
      { maNguoiDung: 1, hoTen: "Quan ly Canteen", email: "admin@uongbigo.vn", matKhau: "Admin@123", vaiTro: "ROLE_ADMIN" },
      { maNguoiDung: 2, hoTen: "Nhan vien bep", email: "bep@uongbigo.vn", matKhau: "Bep@123", vaiTro: "ROLE_STAFF" },
      { maNguoiDung: 3, hoTen: "Sinh vien Demo", email: "sv@uongbigo.vn", matKhau: "sv@123", vaiTro: "ROLE_BUYER" },
    ],
    menu: [
      { maMon: 1, tenMon: "Com tam suon bi", donGia: 32000, moTa: "Com tam suon nuong, bi, cha, trung op la", hinhAnh: "", trangThai: "CON_HANG" },
      { maMon: 2, tenMon: "Bun cha Ha Noi", donGia: 30000, moTa: "Bun, cha nuong than hoa, nuoc mam chua ngot", hinhAnh: "", trangThai: "CON_HANG" },
      { maMon: 3, tenMon: "Pho bo tai", donGia: 35000, moTa: "Pho bo truyen thong, nuoc dung ninh xuong", hinhAnh: "", trangThai: "CON_HANG" },
      { maMon: 4, tenMon: "Bun dau mam tom", donGia: 28000, moTa: "Bun, dau ran, cha com, mam tom nguyen chat", hinhAnh: "", trangThai: "HET_HANG" },
      { maMon: 5, tenMon: "Mi xao hai san", donGia: 33000, moTa: "Mi xao gion cung tom, muc, rau cu", hinhAnh: "", trangThai: "CON_HANG" },
      { maMon: 6, tenMon: "Salad uc ga", donGia: 29000, moTa: "Uc ga ap chao, rau xa lach, sot me rang", hinhAnh: "", trangThai: "CON_HANG" },
    ],
    orders: [],
  };
  saveDB(db);
  return db;
}

function loadDB() {
  try {
    const raw = localStorage.getItem(DB_KEY);
    if (raw) return JSON.parse(raw);
  } catch (e) { /* noop */ }
  return seedDB();
}

function saveDB(db) {
  try { localStorage.setItem(DB_KEY, JSON.stringify(db)); } catch (e) { /* noop */ }
}

function nextId(db, key) {
  db.seq[key] = (db.seq[key] || 0) + 1;
  return db.seq[key];
}

function delay(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function fakeToken(user) {
  return "mock." + btoa(unescape(encodeURIComponent(`${user.maNguoiDung}:${user.email}:${Date.now()}`)));
}

/* Tao anh QR gia lap (SVG) hoan toan o phia client, khong goi mang */
function makeFakeQrDataUri(seedText) {
  let hash = 0;
  for (let i = 0; i < seedText.length; i++) hash = (hash * 31 + seedText.charCodeAt(i)) >>> 0;
  function rand() { hash = (hash * 1103515245 + 12345) >>> 0; return (hash >>> 8) / 16777216; }
  const cells = 21, cellSize = 8, size = cells * cellSize;
  let rects = "";
  for (let y = 0; y < cells; y++) {
    for (let x = 0; x < cells; x++) {
      const inTL = x < 7 && y < 7, inTR = x >= cells - 7 && y < 7, inBL = x < 7 && y >= cells - 7;
      if (inTL || inTR || inBL) continue;
      if (rand() > 0.55) rects += `<rect x="${x * cellSize}" y="${y * cellSize}" width="${cellSize}" height="${cellSize}" fill="#111"/>`;
    }
  }
  function finder(ox, oy) {
    return `<rect x="${ox}" y="${oy}" width="${7 * cellSize}" height="${7 * cellSize}" fill="#111"/>
      <rect x="${ox + cellSize}" y="${oy + cellSize}" width="${5 * cellSize}" height="${5 * cellSize}" fill="#fff"/>
      <rect x="${ox + 2 * cellSize}" y="${oy + 2 * cellSize}" width="${3 * cellSize}" height="${3 * cellSize}" fill="#111"/>`;
  }
  const finders = finder(0, 0) + finder((cells - 7) * cellSize, 0) + finder(0, (cells - 7) * cellSize);
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${size} ${size}" width="240" height="240">
    <rect width="${size}" height="${size}" fill="#fff"/>${rects}${finders}</svg>`;
  return "data:image/svg+xml;utf8," + encodeURIComponent(svg);
}

/* ===================== Bo dinh tuyen "API" gia lap ===================== */
/**
 * Thay the apiFetch that: nhan vao path + options (giong het chu ky cu),
 * nhung xu ly hoan toan o local bang cach doc/ghi vao "database" trong
 * localStorage. Tra ve Promise giong nhu fetch that (co do tre nho cho
 * giong trai nghiem goi mang) va nem loi dang { status, message } khi
 * that bai, dung format ma cac trang khac dang bat (err.message).
 */
window.__UBG_API_SCRIPT_URL = document.currentScript ? document.currentScript.src : location.href;
async function apiFetch(path, options = {}) {
  const [rawPath, queryStr] = path.split("?");
  const qs = new URLSearchParams({ path: rawPath });
  if (queryStr) new URLSearchParams(queryStr).forEach((v,k)=>qs.set(k,v));
  const headers = {"Content-Type":"application/json"};
  const token = Auth.getToken();
  if (token) headers["X-UBG-Token"] = token;
  let response;
  try {
    response = await fetch((new URL("../api.php", window.__UBG_API_SCRIPT_URL || location.href).href) + "?" + qs.toString(), {
      method: (options.method || "GET").toUpperCase(),
      headers,
      credentials: "same-origin",
      body: options.body ? (typeof options.body === "string" ? options.body : JSON.stringify(options.body)) : undefined
    });
  } catch (e) {
    throw {status:0, message:"Khong ket noi duoc may chu XAMPP. Hay mo Apache + MySQL va truy cap qua localhost."};
  }
  let data={}; try { data=await response.json(); } catch(e) {}
  if (!response.ok) throw {status:response.status, message:data.message || "May chu tra ve loi."};
  if (rawPath.startsWith("/thanhtoan/") && data && !data.qrImageBase64 && data.maQr && typeof makeFakeQrDataUri==="function") {
    data.qrImageBase64=makeFakeQrDataUri(data.maQr);
  }
  return data;
}

/* ===================== Tien ich dung chung (giu nguyen nhu ban goc) ===================== */
function formatCurrency(value) {
  return new Intl.NumberFormat("vi-VN").format(Math.round(value)) + "đ";
}

function formatDateTime(isoString) {
  try {
    const d = new Date(isoString);
    return d.toLocaleString("vi-VN", { hour: "2-digit", minute: "2-digit", day: "2-digit", month: "2-digit", year: "numeric" });
  } catch (e) { return isoString; }
}

const STATUS_LABEL = {
  CHO_THANH_TOAN: "Cho thanh toan",
  DA_THANH_TOAN: "Da thanh toan",
  DANG_CHUAN_BI: "Dang chuan bi",
  SAN_SANG_NHAN: "San sang nhan",
  HOAN_THANH: "Hoan thanh",
  DA_HUY: "Da huy",
};

function statusPillHtml(trangThai) {
  const label = STATUS_LABEL[trangThai] || trangThai;
  return `<span class="status-pill status-${trangThai}">${label}</span>`;
}

/* ---------- Banner mat mang (van dung navigator.onLine that cua trinh duyet) ---------- */
function initOfflineBanner() {
  const banner = document.createElement("div");
  banner.className = "offline-banner hidden";
  banner.innerHTML = '<span class="dot"></span><span>Mat ket noi mang - dang cho ket noi lai de dong bo...</span>';
  document.body.appendChild(banner);

  function updateStatus() {
    if (navigator.onLine) {
      banner.classList.add("hidden");
    } else {
      banner.classList.remove("hidden");
    }
  }
  window.addEventListener("online", () => {
    updateStatus();
    window.dispatchEvent(new CustomEvent("ubg:reconnected"));
  });
  window.addEventListener("offline", updateStatus);
  updateStatus();
}

document.addEventListener("DOMContentLoaded", initOfflineBanner);
