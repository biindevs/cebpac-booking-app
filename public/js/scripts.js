// Cebu Pacific Booking Tracker - JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips and popovers if needed
    initializeTooltips();
    initializeFormValidation();
    initializeTableFeatures();
    initializeDeleteModals();
});

// Initialize Bootstrap tooltips
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Form validation on submit
function initializeFormValidation() {
    var forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });
}

// Table features
function initializeTableFeatures() {
    // Delete buttons will now show modal instead
}

// Initialize delete modals
function initializeDeleteModals() {
    // Setup delete buttons
    var deleteButtons = document.querySelectorAll('[data-bs-toggle="modal"][data-bs-target^="#deleteModal"]');
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var targetId = this.getAttribute('data-delete-id');
            var targetType = this.getAttribute('data-delete-type');
            var confirmBtn = document.getElementById('confirmDeleteBtn');
            
            // Update modal title
            var modalTitle = document.getElementById('deleteModalLabel');
            if (targetType === 'account') {
                modalTitle.textContent = 'Delete Account';
            } else if (targetType === 'booking') {
                modalTitle.textContent = 'Delete Booking';
            }
            
            // Update confirm button to submit the form
            confirmBtn.onclick = function() {
                document.getElementById('deleteForm_' + targetId).submit();
            };
        });
    });
}

// Show delete modal
function showDeleteModal(id, type, itemName) {
    var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    var deleteItemName = document.getElementById('deleteItemName');
    var confirmBtn = document.getElementById('confirmDeleteBtn');
    
    // Set item name
    deleteItemName.textContent = itemName + ' (' + type + ')';
    
    // Set confirm button action
    confirmBtn.onclick = function() {
        document.getElementById('deleteForm_' + id).submit();
    };
    
    modal.show();
}

// Add new row to table (placeholder for future functionality)
function addTableRow(tableName) {
    console.log('Adding new row to table:', tableName);
}

// Delete row from table (placeholder for future functionality)
function deleteTableRow(rowId) {
    console.log('Deleting row:', rowId);
}

// Format date picker input
function formatDateInput(inputElement) {
    inputElement.addEventListener('change', function() {
        var date = new Date(this.value);
        console.log('Date selected:', date.toLocaleDateString());
    });
}

