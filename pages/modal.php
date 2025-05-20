<button class="btn-open-modal">Abrir modal</button>
<div class="custom-modal-overlay"></div>
<div class="custom-modal">
    <div class="modal-header">
        <h2></h2>
        <button class="close-modal">X</button>
    </div>
    <div class="modal-content">
    </div>
    <div class="modal-footer">
    </div>
</div>


<script>
    document.querySelectorAll(".btn-open-modal").forEach(button => {
        button.addEventListener("click", () => {
            document.querySelector(".modal-overlay").style.display = "block";
            document.querySelector(".modal").style.display = "block";
        });
    });

    document.querySelector(".close-modal").addEventListener("click", () => {
        document.querySelector(".modal-overlay").style.display = "none";
        document.querySelector(".modal").style.display = "none";
    });
</script>