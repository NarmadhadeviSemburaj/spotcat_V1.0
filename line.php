<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Line Management</title>
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
        
        .dynamic-select {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
  
    <div class="main-container container-fluid">
        <!-- Response Message -->
        <div id="responseMessage" class="alert mb-4"></div>

        <div class="row">
            <!-- Create Line Card -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus-circle me-2"></i>Create New Purchase Line
                    </div>
                    <div class="card-body">
                        <form id="createLineForm">
                            <div class="mb-3">
                                <label for="lineName" class="form-label">Line Name</label>
                                <input type="text" class="form-control" id="lineName" required>
                            </div>
                            <div class="mb-3">
                                <label for="lineLocation" class="form-label">Location</label>
                                <input type="text" class="form-control" id="lineLocation" required>
                            </div>
                            <div class="mb-3">
                                <label for="linePincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="linePincode" required>
                            </div>
                            <div class="mb-3 dynamic-select">
                                <label for="zoneId" class="form-label">Zone</label>
                                <select class="form-select" id="zoneId" required>
                                    <option value="">Select Zone</option>
                                    <!-- Zones will be loaded here -->
                                </select>
                            </div>
                            <div class="mb-3 dynamic-select">
                                <label for="dcmId" class="form-label">DCM</label>
                                <select class="form-select" id="dcmId" required disabled>
                                    <option value="">Select DCM (choose zone first)</option>
                                </select>
                            </div>
                
                            <button type="submit" class="btn btn-primary">
                                <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none"></span>
                                Create Line
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Lines List Card -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-list me-2"></i>Purchase Line List
                        </div>
                        <div class="d-flex">
                            <input type="text" id="searchInput" class="form-control form-control-sm me-2" placeholder="Search lines..." style="width: 200px;">
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
                                        <th>Line Name</th>
                                        <th>Location</th>
                                        <th>Pincode</th>
                                        <th>Zone</th>
                                        <th>DCM</th>
                                        <th>Cluster</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="linesTableBody">
                                    <!-- Lines will be loaded here -->
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

        <!-- Edit Line Modal (updated version) -->
<div class="modal fade" id="editLineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Purchase Line</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="editLineForm">
                    <input type="hidden" id="editLineId">
                    <div class="mb-3">
                        <label for="editLineName" class="form-label">Line Name</label>
                        <input type="text" class="form-control" id="editLineName" required>
                    </div>
                    <div class="mb-3">
                        <label for="editLineLocation" class="form-label">Location</label>
                        <input type="text" class="form-control" id="editLineLocation" required>
                    </div>
                    <div class="mb-3">
                        <label for="editLinePincode" class="form-label">Pincode</label>
                        <input type="text" class="form-control" id="editLinePincode" required>
                    </div>
                    <div class="mb-3 dynamic-select">
                        <label for="editZoneId" class="form-label">Zone</label>
                        <select class="form-select" id="editZoneId" required>
                            <option value="">Select Zone</option>
                            <!-- Zones will be loaded here -->
                        </select>
                    </div>
                    <div class="mb-3 dynamic-select">
                        <label for="editDcmId" class="form-label">DCM</label>
                        <select class="form-select" id="editDcmId" required disabled>
                            <option value="">Select DCM (choose zone first)</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveLineChanges">
                    <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none"></span>
                    Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteLineModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this purchase line? This action cannot be undone.</p>
                        <p><strong>Line ID:</strong> <span id="deleteLineIdText"></span></p>
                        <p><strong>Line Name:</strong> <span id="deleteLineNameText"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteLine">
                            <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none"></span>
                            Delete Line
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
            let zones = [];
            let dcms = [];
            let clusters = [];
            
            // DOM elements
            const linesTableBody = document.getElementById('linesTableBody');
            const pagination = document.getElementById('pagination');
            const perPageSelect = document.getElementById('perPageSelect');
            const searchInput = document.getElementById('searchInput');
            const responseMessage = document.getElementById('responseMessage');
            
            // Initialize the page
            loadInitialData().then(() => {
                loadLines();
            });
            
            // Event listeners
            document.getElementById('createLineForm').addEventListener('submit', createLine);
            perPageSelect.addEventListener('change', function() {
                perPage = parseInt(this.value);
                currentPage = 1;
                loadLines();
            });
            
            searchInput.addEventListener('input', function() {
                searchQuery = this.value.trim();
                currentPage = 1;
                loadLines();
            });
            
            document.getElementById('saveLineChanges').addEventListener('click', updateLine);
            document.getElementById('confirmDeleteLine').addEventListener('click', deleteLine);
            
            // Zone-DCM relationship handling
            document.getElementById('zoneId').addEventListener('change', function() {
                updateDcmDropdown(this.value, 'dcmId');
            });
            
            document.getElementById('editZoneId').addEventListener('change', function() {
                updateDcmDropdown(this.value, 'editDcmId');
            });
            
            // Function to load initial data (zones, dcms, clusters)
            // Updated loadInitialData function
function loadInitialData() {
    return Promise.all([
        fetch('api/zone_api.php?limit=1000')
            .then(response => {
                if (!response.ok) throw new Error('Zone API failed');
                return response.json();
            })
            .then(data => {
                if (data.status !== 'success') throw new Error(data.message || 'Zone data error');
                zones = data.data.zones || [];
                renderDropdown('zoneId', zones, 'zone_id', 'zone_name');
                renderDropdown('editZoneId', zones, 'zone_id', 'zone_name');
                return true;
            })
            .catch(error => {
                console.error('Zone API Error:', error);
                return false;
            }),
        
        fetch('api/dcm_api.php?limit=1000')
            .then(response => {
                if (!response.ok) throw new Error('DCM API failed');
                return response.json();
            })
            .then(data => {
                if (data.status !== 'success') throw new Error(data.message || 'DCM data error');
                dcms = data.data.dcms || [];
                return true;
            })
            .catch(error => {
                console.error('DCM API Error:', error);
                return false;
            }),
        
        fetch('api/cluster.php?limit=1000')
            .then(response => {
                if (!response.ok) throw new Error('Cluster API failed');
                return response.json();
            })
            .then(data => {
                if (data.status !== 'success') throw new Error(data.message || 'Cluster data error');
                clusters = data.data || [];
                renderDropdown('clusterId', clusters, 'cluster_id', 'cluster_name');
                renderDropdown('editClusterId', clusters, 'cluster_id', 'cluster_name');
                return true;
            })
            .catch(error => {
                console.error('Cluster API Error:', error);
                return false;
            })
    ]).then(results => {
        const successCount = results.filter(Boolean).length;
        if (successCount < 3) {
            console.warn(`Only ${successCount}/3 APIs loaded successfully`);
            // Only show error if all APIs failed
            if (successCount === 0) {
                showResponseMessage('error', 'Failed to load initial data. Please refresh the page.');
            }
        }
    });
}
            
            // Function to render dropdown options
            function renderDropdown(elementId, items, valueField, textField) {
                const select = document.getElementById(elementId);
                select.innerHTML = `<option value="">Select ${elementId.replace('Id', '')}</option>`;
                
                items.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item[valueField];
                    option.textContent = item[textField];
                    select.appendChild(option);
                });
            }
            
            // Function to update DCM dropdown based on selected zone
            function updateDcmDropdown(zoneId, dcmSelectId) {
                const dcmSelect = document.getElementById(dcmSelectId);
                dcmSelect.innerHTML = '<option value="">Select DCM</option>';
                
                if (!zoneId) {
                    dcmSelect.disabled = true;
                    return;
                }
                
                const filteredDcms = dcms.filter(dcm => dcm.zone_id == zoneId);
                
                if (filteredDcms.length === 0) {
                    dcmSelect.disabled = true;
                    dcmSelect.innerHTML = '<option value="">No DCMs found for this zone</option>';
                    return;
                }
                
                filteredDcms.forEach(dcm => {
                    const option = document.createElement('option');
                    option.value = dcm.dcm_id;
                    option.textContent = dcm.dcm_name;
                    dcmSelect.appendChild(option);
                });
                
                dcmSelect.disabled = false;
            }
            
            // Function to load lines
            function loadLines() {
                showLoading(true, '#linesTableBody');
                
                let url = `api/line_api.php?page=${currentPage}&limit=${perPage}`;
                if (searchQuery) {
                    url += `&search=${encodeURIComponent(searchQuery)}`;
                }
                
                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            renderLines(data.data.lines);
                            renderPagination(data.data.meta);
                        } else {
                            showResponseMessage('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showResponseMessage('error', 'Failed to load purchase lines. Please try again.');
                    })
                    .finally(() => {
                        showLoading(false, '#linesTableBody');
                    });
            }
            
            // Function to render lines in the table
            function renderLines(lines) {
                linesTableBody.innerHTML = '';
                
                if (lines.length === 0) {
                    linesTableBody.innerHTML = `
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-info-circle me-2"></i>No purchase lines found
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                lines.forEach(line => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${line.line_id}</td>
                        <td>${line.line_name}</td>
                        <td>${line.line_location}</td>
                        <td>${line.line_pincode}</td>
                        <td>${line.zone_name || 'N/A'}</td>
                        <td>${line.dcm_name || 'N/A'}</td>
                        <td>${line.cluster_name || 'N/A'}</td>
                        <td class="action-btns">
                            <button class="btn btn-sm btn-outline-primary edit-line" data-id="${line.line_id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-line" 
                                    data-id="${line.line_id}" 
                                    data-name="${line.line_name}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    `;
                    linesTableBody.appendChild(row);
                });
                
                // Add event listeners to action buttons
                document.querySelectorAll('.edit-line').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const lineId = this.getAttribute('data-id');
                        showEditLineModal(lineId);
                    });
                });
                
                document.querySelectorAll('.delete-line').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const lineId = this.getAttribute('data-id');
                        const lineName = this.getAttribute('data-name');
                        showDeleteConfirmationModal(lineId, lineName);
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
                        loadLines();
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
                        loadLines();
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
                        loadLines();
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
                        loadLines();
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
                        loadLines();
                    }
                });
                pagination.appendChild(nextLi);
            }
            
            // Function to create a new line
            function createLine(e) {
    e.preventDefault();
    
    const lineName = document.getElementById('lineName').value.trim();
    const lineLocation = document.getElementById('lineLocation').value.trim();
    const linePincode = document.getElementById('linePincode').value.trim();
    const zoneId = document.getElementById('zoneId').value;
    const dcmId = document.getElementById('dcmId').value;
    
    if (!lineName || !lineLocation || !linePincode || !zoneId || !dcmId) {
        showResponseMessage('error', 'All fields are required');
        return;
    }
    
    const spinner = e.target.querySelector('.loading-spinner');
    spinner.classList.remove('d-none');
    
    fetch('api/line_api.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            line_name: lineName,
            line_location: lineLocation,
            line_pincode: linePincode,
            zone_id: zoneId,
            dcm_id: dcmId,
            cluster_id: 'CID_001' // Automatically set the cluster name
        })
    })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showResponseMessage('success', data.message);
                        document.getElementById('createLineForm').reset();
                        document.getElementById('dcmId').disabled = true;
                        loadLines();
                    } else {
                        showResponseMessage('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showResponseMessage('error', 'Failed to create purchase line. Please try again.');
                })
                .finally(() => {
                    spinner.classList.add('d-none');
                });
            }
            
            // Function to show edit line modal
            // Function to show edit line modal (updated version)
function showEditLineModal(lineId) {
    fetch(`api/line_api.php?line_id=${lineId}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                const line = data.data;
                
                // Set form values for only the required fields
                document.getElementById('editLineId').value = line.line_id;
                document.getElementById('editLineName').value = line.line_name;
                document.getElementById('editLineLocation').value = line.line_location;
                document.getElementById('editLinePincode').value = line.line_pincode;
                
                // Set zone and update DCM dropdown
                document.getElementById('editZoneId').value = line.zone_id;
                updateDcmDropdown(line.zone_id, 'editDcmId');
                
                // Set DCM after a small delay to allow dropdown to populate
                setTimeout(() => {
                    document.getElementById('editDcmId').value = line.dcm_id;
                }, 100);
                
                const modal = new bootstrap.Modal(document.getElementById('editLineModal'));
                modal.show();
            } else {
                showResponseMessage('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showResponseMessage('error', 'Failed to load purchase line details. Please try again.');
        });
}
            
            // Function to update line
           // Function to update line (updated version)
function updateLine() {
    const lineId = document.getElementById('editLineId').value;
    const lineName = document.getElementById('editLineName').value.trim();
    const lineLocation = document.getElementById('editLineLocation').value.trim();
    const linePincode = document.getElementById('editLinePincode').value.trim();
    const zoneId = document.getElementById('editZoneId').value;
    const dcmId = document.getElementById('editDcmId').value;
    
    if (!lineName || !lineLocation || !linePincode || !zoneId || !dcmId) {
        showResponseMessage('error', 'All fields are required');
        return;
    }
    
    const spinner = document.querySelector('#saveLineChanges .loading-spinner');
    spinner.classList.remove('d-none');
    
    fetch('api/line_api.php', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            line_id: lineId,
            line_name: lineName,
            line_location: lineLocation,
            line_pincode: linePincode,
            zone_id: zoneId,
            dcm_id: dcmId
            // Removed cluster_id as it's not needed in the edit form
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            showResponseMessage('success', 'Purchase line updated successfully');
            loadLines();
            bootstrap.Modal.getInstance(document.getElementById('editLineModal')).hide();
        } else {
            showResponseMessage('error', data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showResponseMessage('error', 'Failed to update purchase line. Please try again.');
    })
    .finally(() => {
        spinner.classList.add('d-none');
    });
}
            // Function to show delete confirmation modal
            function showDeleteConfirmationModal(lineId, lineName) {
                document.getElementById('deleteLineIdText').textContent = lineId;
                document.getElementById('deleteLineNameText').textContent = lineName;
                
                const modal = new bootstrap.Modal(document.getElementById('deleteLineModal'));
                modal.show();
            }
            
            // Function to delete line
            function deleteLine() {
                const lineId = document.getElementById('deleteLineIdText').textContent;
                
                const spinner = document.querySelector('#confirmDeleteLine .loading-spinner');
                spinner.classList.remove('d-none');
                
                fetch('api/line_api.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        line_id: lineId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showResponseMessage('success', data.message);
                        loadLines();
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteLineModal'));
                        modal.hide();
                    } else {
                        showResponseMessage('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showResponseMessage('error', 'Failed to delete purchase line. Please try again.');
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
                            <td colspan="8" class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-2">Loading purchase lines...</p>
                            </td>
                        </tr>
                    `;
                }
            }
        });
    </script>
</body>
</html>