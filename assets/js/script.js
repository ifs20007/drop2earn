document.addEventListener("DOMContentLoaded", () => {
  // Animasi Scroll (Intersection Observer)
  const reveals = document.querySelectorAll(".reveal");
  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("active");
        }
      });
    },
    { threshold: 0.1 },
  );

  reveals.forEach((reveal) => {
    observer.observe(reveal);
  });

  // SCRIPT SCROLLSPY (ACTIVE NAVBAR)
  const sections = document.querySelectorAll(".scroll-section");
  const navLinks = document.querySelectorAll(".nav-item");

  window.addEventListener("scroll", () => {
    let current = "";
    sections.forEach((section) => {
      const sectionTop = section.offsetTop;
      // Deteksi posisi scroll, -150 agar link sudah aktif sedikit sebelum sampai persis di atas
      if (pageYOffset >= sectionTop - 150) {
        current = section.getAttribute("id");
      }
    });

    navLinks.forEach((link) => {
      link.classList.remove("active");
      if (link.getAttribute("href").includes(current) && current !== "") {
        link.classList.add("active");
      }
    });
  });

  // AJAX Cek Ongkir Wilayah
  $("#selectProvinsi").on("change", function () {
    var provinsi_id = $(this).val();
    if (provinsi_id) {
      $("#selectWilayah")
        .html('<option value="">Sedang memuat wilayah...</option>')
        .prop("disabled", true);

      $.ajax({
        type: "POST",
        url: "api_get_wilayah_publik.php",
        data: { id_provinsi: provinsi_id },
        success: function (html) {
          $("#selectWilayah").html(html).prop("disabled", false);
        },
        error: function () {
          $("#selectWilayah")
            .html('<option value="">Gagal mengambil data sistem</option>')
            .prop("disabled", false);
        },
      });
    } else {
      $("#selectWilayah")
        .html('<option value="">-- Pilih Provinsi Dahulu --</option>')
        .prop("disabled", true);
    }
  });
});
