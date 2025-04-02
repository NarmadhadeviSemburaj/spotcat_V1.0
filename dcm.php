<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DCM Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #1e88e5;
            --light-blue: #e3f2fd;
            --dark-blue: #1565c0;
            --ribbon-height: 60px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            padding-top: var(--ribbon-height);
        }
        
        .ribbon {
            background-color: var(--primary-blue);
            color: white;
            height: var(--ribbon-height);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            padding: 0 20px;
        }
        
        .ribbon-title {
            font-size: 1.5rem;
            font-weight: 600;
            margin-right: 30px;
        }
        
        .nav-tabs .nav-link {
            color: rgba(255, 255, 255, 0.8);
            border: none;
            font-weight: 500;
            padding: 10px 15px;
            margin-right: 5px;
        }
        
        .nav-tabs .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.2);
            border-bottom: 3px solid white;
        }
        
        .nav-tabs .nav-link:hover {
            color: white;
        }
        
        .main-container {
            padding: 20px;
            margin-top: 20px;
        }
        
        .card {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
            border: none;
        }
        
        .card-header {
            background-color: white;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
            padding: 15px 20px;
            border-radius: 8px 8px 0 0 !important;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .btn-primary {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-primary:hover {
            background-color: var(--dark-blue);
            border-color: var(--dark-blue);
        }
        
        .btn-outline-primary {
            color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--primary-blue);
            color: white;
        }
        
        .table th {
            background-color: var(--light-blue);
            color: var(--dark-blue);
        }
        
        .action-btns .btn {
            padding: 5px 10px;
            margin-right: 5px;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary-blue);
            border-color: var(--primary-blue);
        }
        
        .pagination .page-link {
            color: var(--primary-blue);
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .status-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-error {
            background-color: #ffebee;
            color: #c62828;
        }
        
        .form-section {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }
        
        #responseMessage {
            display: none;
        }
        
        .loading-spinner {
            display: none;
            color: var(--primary-blue);
        }
    </style>
</head>
<body>
    <div class="main-container container-fluid">
        <!-- Response Message -->
        <div id="responseMessage" class="alert mb-4"></div>

        <div class="row">
            <!-- Create DCM Card -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus-circle me-2"></i>Create New DCM
                    </div>
                    <div class="card-body">
                        <form id="createDCMForm">
                            <div class="mb-3">
                                <label for="dcmName" class="form-label">DCM Name</label>
                                <input type="text" class="form-control" id="dcmName" required>
                            </div>
                            <div class="mb-3">
                                <label for="zoneId" class="form-label">Zone</label>
                                <select class="form-select" id="zoneId" required>
                                    <option value="">Select Zone</option>
                                    <!-- Zones will be loaded here -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="dcmLocation" class="form-label">Location</label>
                                <input type="text" class="form-control" id="dcmLocation" required>
                            </div>
                            <div class="mb-3">
                                <label for="dcmPincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="dcmPincode" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none"></span>
                                Create DCM
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- DCMs List Card -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-list me-2"></i>DCM List
                        </div>
                        <div class="d-flex">
                            <input type="text" id="searchInput" class="form-control form-control-sm me-2" placeholder="Search DCMs..." style="width: 200px;">
                            <select id="perPageSelect" class="form-select form-select-sm" style="width: 80px;">
                                <option value="5">5</option>
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>DCM Name</th>
                                        <th>Zone</th>
                                        <th>Location</th>
                                        <th>Pincode</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="dcmsTableBody">
                                    <!-- DCMs will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination justify-content-center" id="pagination">
                                <!-- Pagination will be loaded here -->
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit DCM Modal -->
        <div class="modal fade" id="editDCMModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit DCM</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editDCMForm">
                            <input type="hidden" id="editDCMId">
                            <div class="mb-3">
                                <label for="editDCMName" class="form-label">DCM Name</label>
                                <input type="text" class="form-control" id="editDCMName" required>
                            </div>
                            <div class="mb-3">
                                <label for="editZoneId" class="form-label">Zone</label>
                                <select class="form-select" id="editZoneId" required>
                                    <option value="">Select Zone</option>
                                    <!-- Zones will be loaded here -->
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="editDCMLocation" class="form-label">Location</label>
                                <input type="text" class="form-control" id="editDCMLocation" required>
                            </div>
                            <div class="mb-3">
                                <label for="editDCMPincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="editDCMPincode" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveDCMChanges">
                            <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none"></span>
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteDCMModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this DCM? This action cannot be undone.</p>
                        <p><strong>DCM ID:</strong> <span id="deleteDCMIdText"></span></p>
                        <p><strong>DCM Name:</strong> <span id="deleteDCMNameText"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteDCM">
                            <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none"></span>
                            Delete DCM
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Global variables
            let currentPage = 1;
            let perPage = 10;
            let totalPages = 1;
            let searchQuery = '';
            let zones = []; // To store fetched zones
            
            // DOM elements
            const dcmsTableBody = document.getElementById('dcmsTableBody');
            const pagination = document.getElementById('pagination');
            const perPageSelect = document.getElementById('perPageSelect');
            const searchInput = document.getElementById('searchInput');
            const responseMessage = document.getElementById('responseMessage');
            const zoneIdSelect = document.getElementById('zoneId');
            const editZoneIdSelect = document.getElementById('editZoneId');
            
            // Initialize the page
            loadZones().then(() => {
                loadDCMs();
            });
            
            // Event listeners
            document.getElementById('createDCMForm').addEventListener('submit', createDCM);
            perPageSelect.addEventListener('change', function() {
                perPage = parseInt(this.value);
                currentPage = 1;
                loadDCMs();
            });
            
            searchInput.addEventListener('input', function() {
                searchQuery = this.value.trim();
                currentPage = 1;
                loadDCMs();
            });
            
            document.getElementById('saveDCMChanges').addEventListener('click', updateDCM);
            document.getElementById('confirmDeleteDCM').addEventListener('click', deleteDCM);
            
            // Function to load zones for dropdowns
            function loadZones() {
                return fetch('api/zone_api.php?limit=1000') // Fetch all zones
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            zones = data.data.zones;
                            renderZoneDropdowns();
                            return true;
                        } else {
                            showResponseMessage('error', 'Failed to load zones: ' + data.message);
                            return false;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading zones:', error);
                        showResponseMessage('error', 'Failed to load zones. Please try again.');
                        return false;
                    });
            }
            
            // Function to render zone dropdowns
            function renderZoneDropdowns() {
                // Clear existing options
                zoneIdSelect.innerHTML = '<option value="">Select Zone</option>';
                editZoneIdSelect.innerHTML = '<option value="">Select Zone</option>';
                
                // Add zones to dropdowns
                zones.forEach(zone => {
                    const option = document.createElement('option');
                    option.value = zone.zone_id;
                    option.textContent = zone.zone_name;
                    zoneIdSelect.appendChild(option.cloneNode(true));
                    editZoneIdSelect.appendChild(option);
                });
            }
            
            // Function to load DCMs
            function loadDCMs() {
                showLoading(true, '#dcmsTableBody');
                
                let url = `api/dcm_api.php?page=${currentPage}&limit=${perPage}`;
                if (searchQuery) {
                    url += `&search=${encodeURIComponent(searchQuery)}`;
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            renderDCMs(data.data.dcms);
                            renderPagination(data.data.meta);
                        } else {
                            showResponseMessage('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showResponseMessage('error', 'Failed to load DCMs. Please try again.');
                    })
                    .finally(() => {
                        showLoading(false, '#dcmsTableBody');
                    });
            }
            
            // Function to render DCMs in the table
            function renderDCMs(dcms) {
                dcmsTableBody.innerHTML = '';
                
                if (dcms.length === 0) {
                    dcmsTableBody.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-info-circle me-2"></i>No DCMs found
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                dcms.forEach(dcm => {
                    const row = document.createElement('tr');
                    const zoneName = zones.find(z => z.zone_id == dcm.zone_id)?.zone_name || 'Unknown';
                    
                    row.innerHTML = `
                        <td>${dcm.dcm_id}</td>
                        <td>${dcm.dcm_name}</td>
                        <td>${zoneName}</td>
                        <td>${dcm.dcm_location}</td>
                        <td>${dcm.dcm_pincode}</td>
                        <td class="action-btns">
                            <button class="btn btn-sm btn-outline-primary edit-dcm" data-id="${dcm.dcm_id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-dcm" data-id="${dcm.dcm_id}" data-name="${dcm.dcm_name}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    `;
                    dcmsTableBody.appendChild(row);
                });
                
                // Add event listeners to edit and delete buttons
                document.querySelectorAll('.edit-dcm').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const dcmId = this.getAttribute('data-id');
                        showEditDCMModal(dcmId);
                    });
                });
                
                document.querySelectorAll('.delete-dcm').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const dcmId = this.getAttribute('data-id');
                        const dcmName = this.getAttribute('data-name');
                        showDeleteConfirmationModal(dcmId, dcmName);
                    });
                });
            }
            
            // Function to render pagination
            function renderPagination(meta) {
                pagination.innerHTML = '';
                totalPages = meta.total_pages;
                
                if (totalPages <= 1) return;
                
                // Previous button
                const prevLi = document.createElement('li');
                prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
                prevLi.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        loadDCMs();
                    }
                });
                pagination.appendChild(prevLi);
                
                // Page numbers
                const startPage = Math.max(1, currentPage - 2);
                const endPage = Math.min(totalPages, currentPage + 2);
                
                if (startPage > 1) {
                    const firstLi = document.createElement('li');
                    firstLi.className = 'page-item';
                    firstLi.innerHTML = `<a class="page-link" href="#">1</a>`;
                    firstLi.addEventListener('click', function(e) {
                        e.preventDefault();
                        currentPage = 1;
                        loadDCMs();
                    });
                    pagination.appendChild(firstLi);
                    
                    if (startPage > 2) {
                        const ellipsisLi = document.createElement('li');
                        ellipsisLi.className = 'page-item disabled';
                        ellipsisLi.innerHTML = `<span class="page-link">...</span>`;
                        pagination.appendChild(ellipsisLi);
                    }
                }
                
                for (let i = startPage; i <= endPage; i++) {
                    const pageLi = document.createElement('li');
                    pageLi.className = `page-item ${i === currentPage ? 'active' : ''}`;
                    pageLi.innerHTML = `<a class="page-link" href="#">${i}</a>`;
                    pageLi.addEventListener('click', function(e) {
                        e.preventDefault();
                        currentPage = i;
                        loadDCMs();
                    });
                    pagination.appendChild(pageLi);
                }
                
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        const ellipsisLi = document.createElement('li');
                        ellipsisLi.className = 'page-item disabled';
                        ellipsisLi.innerHTML = `<span class="page-link">...</span>`;
                        pagination.appendChild(ellipsisLi);
                    }
                    
                    const lastLi = document.createElement('li');
                    lastLi.className = 'page-item';
                    lastLi.innerHTML = `<a class="page-link" href="#">${totalPages}</a>`;
                    lastLi.addEventListener('click', function(e) {
                        e.preventDefault();
                        currentPage = totalPages;
                        loadDCMs();
                    });
                    pagination.appendChild(lastLi);
                }
                
                // Next button
                const nextLi = document.createElement('li');
                nextLi.className = `page-item ${currentPage === totalPages ? 'disabled' : ''}`;
                nextLi.innerHTML = `<a class="page-link" href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a>`;
                nextLi.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        currentPage++;
                        loadDCMs();
                    }
                });
                pagination.appendChild(nextLi);
            }
            
            // Function to create a new DCM
            function createDCM(e) {
                e.preventDefault();
                
                const dcmName = document.getElementById('dcmName').value.trim();
                const zoneId = document.getElementById('zoneId').value;
                const dcmLocation = document.getElementById('dcmLocation').value.trim();
                const dcmPincode = document.getElementById('dcmPincode').value.trim();
                
                if (!dcmName || !zoneId || !dcmLocation || !dcmPincode) {
                    showResponseMessage('error', 'All fields are required');
                    return;
                }
                
                const spinner = e.target.querySelector('.loading-spinner');
                spinner.classList.remove('d-none');
                
                fetch('api/dcm_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        dcm_name: dcmName,
                        zone_id: zoneId,
                        dcm_location: dcmLocation,
                        dcm_pincode: dcmPincode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showResponseMessage('success', data.message);
                        document.getElementById('createDCMForm').reset();
                        loadDCMs();
                    } else {
                        showResponseMessage('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showResponseMessage('error', 'Failed to create DCM. Please try again.');
                })
                .finally(() => {
                    spinner.classList.add('d-none');
                });
            }
            
            // Function to show edit DCM modal
            function showEditDCMModal(dcmId) {
                fetch(`api/dcm_api.php?dcm_id=${dcmId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const dcm = data.data;
                            document.getElementById('editDCMId').value = dcm.dcm_id;
                            document.getElementById('editDCMName').value = dcm.dcm_name;
                            document.getElementById('editZoneId').value = dcm.zone_id;
                            document.getElementById('editDCMLocation').value = dcm.dcm_location;
                            document.getElementById('editDCMPincode').value = dcm.dcm_pincode;
                            
                            const modal = new bootstrap.Modal(document.getElementById('editDCMModal'));
                            modal.show();
                        } else {
                            showResponseMessage('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showResponseMessage('error', 'Failed to load DCM details. Please try again.');
                    });
            }
            
            // Function to update DCM
            function updateDCM() {
                const dcmId = document.getElementById('editDCMId').value;
                const dcmName = document.getElementById('editDCMName').value.trim();
                const zoneId = document.getElementById('editZoneId').value;
                const dcmLocation = document.getElementById('editDCMLocation').value.trim();
                const dcmPincode = document.getElementById('editDCMPincode').value.trim();
                
                if (!dcmName || !zoneId || !dcmLocation || !dcmPincode) {
                    showResponseMessage('error', 'All fields are required');
                    return;
                }
                
                const payload = {
                    dcm_id: dcmId,
                    dcm_name: dcmName,
                    zone_id: zoneId,
                    dcm_location: dcmLocation,
                    dcm_pincode: dcmPincode
                };
                
                const spinner = document.querySelector('#saveDCMChanges .loading-spinner');
                spinner.classList.remove('d-none');
                
                fetch('api/dcm_api.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(payload)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showResponseMessage('success', 'DCM updated successfully');
                        loadDCMs();
                        bootstrap.Modal.getInstance(document.getElementById('editDCMModal')).hide();
                    } else {
                        showResponseMessage('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showResponseMessage('error', 'Failed to update DCM. Please try again.');
                })
                .finally(() => {
                    spinner.classList.add('d-none');
                });
            }
            
            // Function to show delete confirmation modal
            function showDeleteConfirmationModal(dcmId, dcmName) {
                document.getElementById('deleteDCMIdText').textContent = dcmId;
                document.getElementById('deleteDCMNameText').textContent = dcmName;
                
                const modal = new bootstrap.Modal(document.getElementById('deleteDCMModal'));
                modal.show();
            }
            
            // Function to delete DCM
            function deleteDCM() {
                const dcmId = document.getElementById('deleteDCMIdText').textContent;
                
                const spinner = document.querySelector('#confirmDeleteDCM .loading-spinner');
                spinner.classList.remove('d-none');
                
                fetch('api/dcm_api.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        dcm_id: dcmId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showResponseMessage('success', data.message);
                        loadDCMs();
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteDCMModal'));
                        modal.hide();
                    } else {
                        showResponseMessage('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showResponseMessage('error', 'Failed to delete DCM. Please try again.');
                })
                .finally(() => {
                    spinner.classList.add('d-none');
                });
            }
            
            // Function to show response message
            function showResponseMessage(type, message) {
                responseMessage.style.display = 'block';
                responseMessage.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
                responseMessage.innerHTML = `
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close float-end" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    responseMessage.style.display = 'none';
                }, 5000);
            }
            
            // Function to show/hide loading state
            function showLoading(show, elementSelector) {
                const element = document.querySelector(elementSelector);
                if (show) {
                    element.innerHTML = `
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading DCMs...</p>
                            </td>
                        </tr>
                    `;
                }
            }
        });
    </script>
</body>
</html>