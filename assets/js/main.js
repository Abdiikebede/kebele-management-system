document.addEventListener("DOMContentLoaded", function () {
  // Password toggle functionality
  const passwordToggles = document.querySelectorAll(".password-toggle");

  passwordToggles.forEach((toggle) => {
    toggle.addEventListener("click", function () {
      const input = this.previousElementSibling;
      const type =
        input.getAttribute("type") === "password" ? "text" : "password";
      input.setAttribute("type", type);
      this.classList.toggle("fa-eye");
      this.classList.toggle("fa-eye-slash");
    });
  });

  // Form validation
  const forms = document.querySelectorAll("form[needs-validation]");

  forms.forEach((form) => {
    form.addEventListener(
      "submit",
      function (event) {
        if (!this.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }

        this.classList.add("was-validated");
      },
      false
    );
  });

  // Toast notifications
  const toastElList = [].slice.call(document.querySelectorAll(".toast"));
  const toastList = toastElList.map(function (toastEl) {
    return new bootstrap.Toast(toastEl);
  });

  toastList.forEach((toast) => toast.show());
});
