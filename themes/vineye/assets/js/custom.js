document.addEventListener("DOMContentLoaded", function() {
    console.log("Page loaded!");
    let elements = document.querySelectorAll(".btn");
    elements.forEach(element => {
        element.addEventListener("click", function() {
            alert("Button clicked!");
        });
    });
});
