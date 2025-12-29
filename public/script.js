// script.js

// Xác nhận khi xóa
function confirmDelete(message) {
    return confirm(message || "Bạn có chắc chắn muốn xóa?");
}

// Toggle menu trên mobile
document.addEventListener("DOMContentLoaded", function () {
    const nav = document.querySelector("nav");
    const toggleBtn = document.createElement("button");
    toggleBtn.innerText = "☰ Menu";
    toggleBtn.className = "menu-toggle";
    nav.parentNode.insertBefore(toggleBtn, nav);

    toggleBtn.addEventListener("click", function () {
        nav.classList.toggle("open");
    });
});

// Hiển thị thông báo (toast)
function showToast(msg, type = "success") {
    const toast = document.createElement("div");
    toast.className = "toast " + type;
    toast.innerText = msg;
    document.body.appendChild(toast);

    setTimeout(() => {
        toast.classList.add("show");
    }, 100);

    setTimeout(() => {
        toast.classList.remove("show");
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

// Ví dụ: gọi showToast khi form submit thành công
document.addEventListener("submit", function (e) {
    if (e.target.tagName === "FORM") {
        showToast("Thao tác thành công!", "success");
    }
});

// Highlight dòng khi hover
document.addEventListener("mouseover", function (e) {
    if (e.target.closest("tr")) {
        e.target.closest("tr").style.backgroundColor = "#f0f8ff";
    }
});
document.addEventListener("mouseout", function (e) {
    if (e.target.closest("tr")) {
        e.target.closest("tr").style.backgroundColor = "";
    }
});
// Load dữ liệu lịch hẹn bằng AJAX
function loadAppointments() {
    fetch('appointments_data.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.querySelector("#appointmentsTable tbody");
            tbody.innerHTML = "";
            data.forEach(a => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${a.id}</td>
                    <td>${a.patient_name}</td>
                    <td>${a.doctor_name}</td>
                    <td>${a.service_name}</td>
                    <td>${a.date}</td>
                    <td>${a.time}</td>
                    <td>${a.note || ""}</td>
                    <td>${a.status}</td>
                `;
                tbody.appendChild(tr);
            });
        })
        .catch(err => {
            console.error("Lỗi load dữ liệu:", err);
        });
}

// Gọi load khi trang sẵn sàng
document.addEventListener("DOMContentLoaded", loadAppointments);

