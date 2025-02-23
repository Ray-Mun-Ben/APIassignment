document.addEventListener("DOMContentLoaded", function () {
    // ✅ Define Progress Based on the Current Page
    let progress = 0;
    let currentPage = window.location.pathname.split("/").pop();

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

    // ✅ Animate Progress Bar
    document.getElementById("progressBar").style.width = progress + "%";
});
document.addEventListener("DOMContentLoaded", function () {
    const progressBar = document.getElementById("progressBar");

    let currentStep = 1;
    if (window.location.href.includes("extras.php")) currentStep = 2;
    if (window.location.href.includes("UserAcc.php")) currentStep = 3;
    if (window.location.href.includes("receipt.php")) currentStep = 4;

    const progressWidth = (currentStep - 1) * 33 + "%";
    progressBar.style.width = progressWidth;
});
