document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("birthCertificateForm");
  const purposeSelect = document.getElementById("purpose");
  const otherPurposeContainer = document.getElementById(
    "otherPurposeContainer"
  );

  // Show/hide other purpose field based on selection
  purposeSelect.addEventListener("change", function () {
    if (this.value === "other") {
      otherPurposeContainer.style.display = "block";
      document
        .getElementById("otherPurpose")
        .setAttribute("required", "required");
    } else {
      otherPurposeContainer.style.display = "none";
      document.getElementById("otherPurpose").removeAttribute("required");
    }
  });

  // Form validation before submission
  form.addEventListener("submit", function (event) {
    // Check if all required fields are filled
    const requiredFields = form.querySelectorAll("[required]");
    let isValid = true;

    requiredFields.forEach((field) => {
      if (!field.value.trim()) {
        isValid = false;
        field.classList.add("is-invalid");
      } else {
        field.classList.remove("is-invalid");
      }
    });

    // Validate email format
    const emailField = document.getElementById("email");
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(emailField.value)) {
      isValid = false;
      emailField.classList.add("is-invalid");
    } else {
      emailField.classList.remove("is-invalid");
    }

    // Validate file size (max 5MB)
    const fileInput = document.getElementById("proofDocument");
    if (fileInput.files.length > 0) {
      const fileSize = fileInput.files[0].size / 1024 / 1024; // in MB
      if (fileSize > 5) {
        isValid = false;
        fileInput.classList.add("is-invalid");
        alert("File size exceeds 5MB limit. Please upload a smaller file.");
      } else {
        fileInput.classList.remove("is-invalid");
      }
    }

    if (!isValid) {
      event.preventDefault();
      alert("Please fill out all required fields correctly.");
    } else {
      // Disable submit button to prevent multiple submissions
      document.getElementById("submitBtn").disabled = true;
      document.getElementById("submitBtn").innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Processing...';
    }
  });

  // Real-time validation for fields
  form.querySelectorAll("input, select, textarea").forEach((field) => {
    field.addEventListener("input", function () {
      if (this.hasAttribute("required") && !this.value.trim()) {
        this.classList.add("is-invalid");
      } else {
        this.classList.remove("is-invalid");
      }
    });
  });
});
