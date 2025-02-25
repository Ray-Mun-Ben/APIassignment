document.addEventListener("DOMContentLoaded", function () {
    const progressBar = document.getElementById("progressBar");
    
    if (!progressBar) return; // ✅ Prevent errors if element is missing

    let progress = 0;
    let currentPage = window.location.pathname.split("/").pop();

    // ✅ Define Progress Based on the Current Page
    if (currentPage === "grid.php") {
        progress = 25;
        document.getElementById("step1").classList.add("active");
    } else if (currentPage === "extras.php") {
        progress = 50;
        document.getElementById("step2").classList.add("active");
    } else if (currentPage === "UserAcc.php") {
        progress = 75;
        document.getElementById("step3").classList.add("active");
    } else if (currentPage === "receipt.php") {
        progress = 100;
        document.getElementById("step4").classList.add("active");
    }

    // ✅ Animate Progress Bar Smoothly
    setTimeout(() => {
        progressBar.style.width = progress + "%";
    }, 200);
});
