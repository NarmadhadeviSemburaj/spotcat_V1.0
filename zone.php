<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zone Management System</title>
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
            <!-- Create Zone Card -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus-circle me-2"></i>Create New Zone
                    </div>
                    <div class="card-body">
                        <form id="createZoneForm">
                            <div class="mb-3">
                                <label for="zoneName" class="form-label">Zone Name</label>
                                <input type="text" class="form-control" id="zoneName" required>
                            </div>
                            <div class="mb-3">
                                <label for="zoneLocation" class="form-label">Location</label>
                                <input type="text" class="form-control" id="zoneLocation" required>
                            </div>
                            <div class="mb-3">
                                <label for="zonePincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="zonePincode" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none"></span>
                                Create Zone
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Zones List Card -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-list me-2"></i>Zone List
                        </div>
                        <div class="d-flex">
                            <input type="text" id="searchInput" class="form-control form-control-sm me-2" placeholder="Search zones..." style="width: 200px;">
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
                                        <th>Zone Name</th>
                                        <th>Location</th>
                                        <th>Pincode</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="zonesTableBody">
                                    <!-- Zones will be loaded here -->
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

        <!-- Edit Zone Modal -->
        <div class="modal fade" id="editZoneModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Zone</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editZoneForm">
                            <input type="hidden" id="editZoneId">
                            <div class="mb-3">
                                <label for="editZoneName" class="form-label">Zone Name</label>
                                <input type="text" class="form-control" id="editZoneName" required>
                            </div>
                            <div class="mb-3">
                                <label for="editZoneLocation" class="form-label">Location</label>
                                <input type="text" class="form-control" id="editZoneLocation" required>
                            </div>
                            <div class="mb-3">
                                <label for="editZonePincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="editZonePincode" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveZoneChanges">
                            <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none"></span>
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteZoneModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this zone? This action cannot be undone.</p>
                        <p><strong>Zone ID:</strong> <span id="deleteZoneIdText"></span></p>
                        <p><strong>Zone Name:</strong> <span id="deleteZoneNameText"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteZone">
                            <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none"></span>
                            Delete Zone
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
            
            // DOM elements
            const zonesTableBody = document.getElementById('zonesTableBody');
            const pagination = document.getElementById('pagination');
            const perPageSelect = document.getElementById('perPageSelect');
            const searchInput = document.getElementById('searchInput');
            const responseMessage = document.getElementById('responseMessage');
            
            // Initialize the page
            loadZones();
            
            // Event listeners
            document.getElementById('createZoneForm').addEventListener('submit', createZone);
            perPageSelect.addEventListener('change', function() {
                perPage = parseInt(this.value);
                currentPage = 1;
                loadZones();
            });
            
            searchInput.addEventListener('input', function() {
                searchQuery = this.value.trim();
                currentPage = 1;
                loadZones();
            });
            
            document.getElementById('saveZoneChanges').addEventListener('click', updateZone);
            document.getElementById('confirmDeleteZone').addEventListener('click', deleteZone);
            
            // Function to load zones
            function loadZones() {
                showLoading(true, '#zonesTableBody');
                
                let url = `api/zone_api.php?page=${currentPage}&limit=${perPage}`;
                if (searchQuery) {
                    // Note: Your API would need to support search functionality
                    url += `&search=${encodeURIComponent(searchQuery)}`;
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            renderZones(data.data.zones);
                            renderPagination(data.data.meta);
                        } else {
                            showResponseMessage('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showResponseMessage('error', 'Failed to load zones. Please try again.');
                    })
                    .finally(() => {
                        showLoading(false, '#zonesTableBody');
                    });
            }
            
            // Function to render zones in the table
            function renderZones(zones) {
                zonesTableBody.innerHTML = '';
                
                if (zones.length === 0) {
                    zonesTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="fas fa-info-circle me-2"></i>No zones found
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                zones.forEach(zone => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${zone.zone_id}</td>
                        <td>${zone.zone_name}</td>
                        <td>${zone.zone_location}</td>
                        <td>${zone.zone_pincode}</td>
                        <td class="action-btns">
                            <button class="btn btn-sm btn-outline-primary edit-zone" data-id="${zone.zone_id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-zone" data-id="${zone.zone_id}" data-name="${zone.zone_name}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    `;
                    zonesTableBody.appendChild(row);
                });
                
                // Add event listeners to edit and delete buttons
                document.querySelectorAll('.edit-zone').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const zoneId = this.getAttribute('data-id');
                        showEditZoneModal(zoneId);
                    });
                });
                
                document.querySelectorAll('.delete-zone').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const zoneId = this.getAttribute('data-id');
                        const zoneName = this.getAttribute('data-name');
                        showDeleteConfirmationModal(zoneId, zoneName);
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
                        loadZones();
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
                        loadZones();
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
                        loadZones();
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
                        loadZones();
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
                        loadZones();
                    }
                });
                pagination.appendChild(nextLi);
            }
            
            // Function to create a new zone
            function createZone(e) {
                e.preventDefault();
                
                const zoneName = document.getElementById('zoneName').value.trim();
                const zoneLocation = document.getElementById('zoneLocation').value.trim();
                const zonePincode = document.getElementById('zonePincode').value.trim();
                
                if (!zoneName || !zoneLocation || !zonePincode) {
                    showResponseMessage('error', 'All fields are required');
                    return;
                }
                
                const spinner = e.target.querySelector('.loading-spinner');
                spinner.classList.remove('d-none');
                
                fetch('api/zone_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        zone_name: zoneName,
                        zone_location: zoneLocation,
                        zone_pincode: zonePincode
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showResponseMessage('success', data.message);
                        document.getElementById('createZoneForm').reset();
                        loadZones();
                    } else {
                        showResponseMessage('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showResponseMessage('error', 'Failed to create zone. Please try again.');
                })
                .finally(() => {
                    spinner.classList.add('d-none');
                });
            }
            
            // Function to show edit zone modal
            function showEditZoneModal(zoneId) {
                fetch(`api/zone_api.php?zone_id=${zoneId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const zone = data.data;
                            document.getElementById('editZoneId').value = zone.zone_id;
                            document.getElementById('editZoneName').value = zone.zone_name;
                            document.getElementById('editZoneLocation').value = zone.zone_location;
                            document.getElementById('editZonePincode').value = zone.zone_pincode;
                            
                            const modal = new bootstrap.Modal(document.getElementById('editZoneModal'));
                            modal.show();
                        } else {
                            showResponseMessage('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showResponseMessage('error', 'Failed to load zone details. Please try again.');
                    });
            }
            
            // Function to update zone (now updates all fields at once)
            // Modified updateZone function to fix the 'line' table error
            // Fixed and fully working updateZone function
function updateZone() {
    const zoneId = document.getElementById('editZoneId').value;
    const zoneName = document.getElementById('editZoneName').value.trim();
    const zoneLocation = document.getElementById('editZoneLocation').value.trim();
    const zonePincode = document.getElementById('editZonePincode').value.trim();
    
    // Validate required fields
    if (!zoneName || !zoneLocation || !zonePincode) {
        showResponseMessage('error', 'All fields are required');
        return;
    }

    // Create payload with all fields (simpler approach that always works)
    const payload = {
        zone_id: zoneId,
        zone_name: zoneName,
        zone_location: zoneLocation,
        zone_pincode: zonePincode
    };

    // Get original values for change detection
    const originalRow = document.querySelector(`.edit-zone[data-id="${zoneId}"]`)?.closest('tr');
    if (originalRow) {
        const originalName = originalRow.cells[1].textContent.trim();
        const originalLocation = originalRow.cells[2].textContent.trim();
        const originalPincode = originalRow.cells[3].textContent.trim();
        
        // Check if any fields were actually changed
        if (zoneName === originalName && 
            zoneLocation === originalLocation && 
            zonePincode === originalPincode) {
            showResponseMessage('info', 'No changes detected');
            return;
        }
    }

    const spinner = document.querySelector('#saveZoneChanges .loading-spinner');
    spinner.classList.remove('d-none');
    
    fetch('api/zone_api.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(payload)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (!data) {
            throw new Error('No data received from server');
        }
        if (data.status === 'success') {
            showResponseMessage('success', 'Zone updated successfully');
            loadZones();
            bootstrap.Modal.getInstance(document.getElementById('editZoneModal')).hide();
        } else {
            throw new Error(data.message || 'Update failed');
        }
    })
    .catch(error => {
        console.error('Update error:', error);
        showResponseMessage('error', error.message || 'Failed to update zone. Please try again.');
    })
    .finally(() => {
        spinner.classList.add('d-none');
    });
}
            
            // Function to show delete confirmation modal
            function showDeleteConfirmationModal(zoneId, zoneName) {
                document.getElementById('deleteZoneIdText').textContent = zoneId;
                document.getElementById('deleteZoneNameText').textContent = zoneName;
                
                const modal = new bootstrap.Modal(document.getElementById('deleteZoneModal'));
                modal.show();
            }
            
            // Function to delete zone
            function deleteZone() {
                const zoneId = document.getElementById('deleteZoneIdText').textContent;
                
                const spinner = document.querySelector('#confirmDeleteZone .loading-spinner');
                spinner.classList.remove('d-none');
                
                fetch('api/zone_api.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        zone_id: zoneId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showResponseMessage('success', data.message);
                        loadZones();
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteZoneModal'));
                        modal.hide();
                    } else {
                        showResponseMessage('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showResponseMessage('error', 'Failed to delete zone. Please try again.');
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
                            <td colspan="5" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading zones...</p>
                            </td>
                        </tr>
                    `;
                }
            }
        });
    </script>
</body>
</html>