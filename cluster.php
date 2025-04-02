<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cluster Management System</title>
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
        
        .status-active {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .status-inactive {
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
        
        .status-toggle {
            cursor: pointer;
        }
    </style>
</head>
<body>
    

    <div class="main-container container-fluid">
        <!-- Response Message -->
        <div id="responseMessage" class="alert mb-4"></div>

        <div class="row">
            <!-- Create Cluster Card -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus-circle me-2"></i>Create New Cluster
                    </div>
                    <div class="card-body">
                        <form id="createClusterForm">
                            <div class="mb-3">
                                <label for="clusterName" class="form-label">Cluster Name</label>
                                <input type="text" class="form-control" id="clusterName" required>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none"></span>
                                Create Cluster
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Clusters List Card -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-list me-2"></i>Cluster List
                        </div>
                        <div class="d-flex">
                            <input type="text" id="searchInput" class="form-control form-control-sm me-2" placeholder="Search clusters..." style="width: 200px;">
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
                                        <th>Cluster Name</th>
                                        <th>Status</th>
                                        <th>Created At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="clustersTableBody">
                                    <!-- Clusters will be loaded here -->
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

        <!-- Edit Cluster Modal -->
        <div class="modal fade" id="editClusterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Cluster</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editClusterForm">
                            <input type="hidden" id="editClusterId">
                            <div class="mb-3">
                                <label for="editClusterName" class="form-label">Cluster Name</label>
                                <input type="text" class="form-control" id="editClusterName" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="editClusterStatus" style="width: 3em; height: 1.5em;">
                                    <label class="form-check-label" for="editClusterStatus" id="statusLabel">Active</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveClusterChanges">
                            <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none"></span>
                            Save Changes
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteClusterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Confirm Deletion</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this cluster? This action cannot be undone.</p>
                        <p><strong>Cluster ID:</strong> <span id="deleteClusterIdText"></span></p>
                        <p><strong>Cluster Name:</strong> <span id="deleteClusterNameText"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDeleteCluster">
                            <span class="loading-spinner spinner-border spinner-border-sm me-2 d-none"></span>
                            Delete Cluster
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
            const clustersTableBody = document.getElementById('clustersTableBody');
            const pagination = document.getElementById('pagination');
            const perPageSelect = document.getElementById('perPageSelect');
            const searchInput = document.getElementById('searchInput');
            const responseMessage = document.getElementById('responseMessage');
            const statusLabel = document.getElementById('statusLabel');
            
            // Initialize the page
            loadClusters();
            
            // Event listeners
            document.getElementById('createClusterForm').addEventListener('submit', createCluster);
            perPageSelect.addEventListener('change', function() {
                perPage = parseInt(this.value);
                currentPage = 1;
                loadClusters();
            });
            
            searchInput.addEventListener('input', function() {
                searchQuery = this.value.trim();
                currentPage = 1;
                loadClusters();
            });
            
            document.getElementById('saveClusterChanges').addEventListener('click', updateCluster);
            document.getElementById('confirmDeleteCluster').addEventListener('click', deleteCluster);
            
            // Status toggle event
            document.getElementById('editClusterStatus').addEventListener('change', function() {
                statusLabel.textContent = this.checked ? 'Active' : 'Inactive';
            });
            
            // Function to load clusters
            function loadClusters() {
    showLoading(true, '#clustersTableBody');
    
    fetch('api/cluster_api.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Handle the API's response format
            if (data.status && data.status === 'error') {
                throw new Error(data.message);
            }
            
            // The actual clusters data is in data.data for successful responses
            const clusters = data.data || data;
            
            if (!Array.isArray(clusters)) {
                throw new Error('Invalid data format received from server');
            }
            
            renderClusters(clusters);
            
            // Since your API doesn't support pagination, we'll use client-side pagination
            // or just show all clusters (remove pagination controls)
            renderPagination({
                total_pages: 1,
                total_items: clusters.length
            });
        })
        .catch(error => {
            console.error('Error loading clusters:', error);
            // Only show error message if it's not a network error
            if (error.message !== 'Failed to fetch') {
                showResponseMessage('error', error.message || 'Failed to load clusters');
            }
        })
        .finally(() => {
            showLoading(false, '#clustersTableBody');
        });
}




            // Function to render clusters in the table
            function renderClusters(clusters) {
                clustersTableBody.innerHTML = '';
                
                if (clusters.length === 0) {
                    clustersTableBody.innerHTML = `
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                <i class="fas fa-info-circle me-2"></i>No clusters found
                            </td>
                        </tr>
                    `;
                    return;
                }
                
                clusters.forEach(cluster => {
                    const row = document.createElement('tr');
                    const createdDate = new Date(cluster.created_at).toLocaleString();
                    const statusClass = cluster.status === 'active' ? 'status-active' : 'status-inactive';
                    
                    row.innerHTML = `
                        <td>${cluster.cluster_id}</td>
                        <td>${cluster.cluster_name}</td>
                        <td>
                            <span class="status-badge ${statusClass}">
                                <i class="fas ${cluster.status === 'active' ? 'fa-check-circle' : 'fa-times-circle'} me-1"></i>
                                ${cluster.status}
                            </span>
                        </td>
                        <td>${createdDate}</td>
                        <td class="action-btns">
                            <button class="btn btn-sm btn-outline-primary edit-cluster" data-id="${cluster.cluster_id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-cluster" data-id="${cluster.cluster_id}" data-name="${cluster.cluster_name}">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            <button class="btn btn-sm ${cluster.status === 'active' ? 'btn-outline-warning' : 'btn-outline-success'} toggle-status" 
                                    data-id="${cluster.cluster_id}" 
                                    data-status="${cluster.status}">
                                <i class="fas ${cluster.status === 'active' ? 'fa-toggle-off' : 'fa-toggle-on'}"></i>
                            </button>
                        </td>
                    `;
                    clustersTableBody.appendChild(row);
                });
                
                // Add event listeners to action buttons
                document.querySelectorAll('.edit-cluster').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const clusterId = this.getAttribute('data-id');
                        showEditClusterModal(clusterId);
                    });
                });
                
                document.querySelectorAll('.delete-cluster').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const clusterId = this.getAttribute('data-id');
                        const clusterName = this.getAttribute('data-name');
                        showDeleteConfirmationModal(clusterId, clusterName);
                    });
                });
                
                document.querySelectorAll('.toggle-status').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const clusterId = this.getAttribute('data-id');
                        const currentStatus = this.getAttribute('data-status');
                        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
                        toggleClusterStatus(clusterId, newStatus);
                    });
                });
            }
            
            // Function to render pagination
            function renderPagination(meta) {
                pagination.innerHTML = '';
                totalPages = meta.total_pages || 1;
                
                if (meta.total_pages <= 1 || meta.total_items <= perPage) {
        return;
    }
                
                // Previous button
                const prevLi = document.createElement('li');
                prevLi.className = `page-item ${currentPage === 1 ? 'disabled' : ''}`;
                prevLi.innerHTML = `<a class="page-link" href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a>`;
                prevLi.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        loadClusters();
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
                        loadClusters();
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
                        loadClusters();
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
                        loadClusters();
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
                        loadClusters();
                    }
                });
                pagination.appendChild(nextLi);
            }
            
            // Function to create a new cluster
            function createCluster(e) {
                e.preventDefault();
                
                const clusterName = document.getElementById('clusterName').value.trim();
                
                if (!clusterName) {
                    showResponseMessage('error', 'Cluster name is required');
                    return;
                }
                
                const spinner = e.target.querySelector('.loading-spinner');
                spinner.classList.remove('d-none');
                
                fetch('api/cluster_api.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cluster_name: clusterName
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showResponseMessage('success', data.message);
                        document.getElementById('createClusterForm').reset();
                        loadClusters();
                    } else {
                        showResponseMessage('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showResponseMessage('error', 'Failed to create cluster. Please try again.');
                })
                .finally(() => {
                    spinner.classList.add('d-none');
                });
            }
            
            // Function to show edit cluster modal
            function showEditClusterModal(clusterId) {
                fetch(`api/cluster_api.php?cluster_id=${clusterId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const cluster = data.data;
                            document.getElementById('editClusterId').value = cluster.cluster_id;
                            document.getElementById('editClusterName').value = cluster.cluster_name;
                            document.getElementById('editClusterStatus').checked = cluster.status === 'active';
                            statusLabel.textContent = cluster.status;
                            
                            const modal = new bootstrap.Modal(document.getElementById('editClusterModal'));
                            modal.show();
                        } else {
                            showResponseMessage('error', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showResponseMessage('error', 'Failed to load cluster details. Please try again.');
                    });
            }
            
            // Function to update cluster
            function updateCluster() {
                const clusterId = document.getElementById('editClusterId').value;
                const clusterName = document.getElementById('editClusterName').value.trim();
                const status = document.getElementById('editClusterStatus').checked ? 'active' : 'inactive';
                
                if (!clusterName) {
                    showResponseMessage('error', 'Cluster name is required');
                    return;
                }
                
                const spinner = document.querySelector('#saveClusterChanges .loading-spinner');
                spinner.classList.remove('d-none');
                
                fetch('api/cluster_api.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cluster_id: clusterId,
                        cluster_name: clusterName,
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showResponseMessage('success', 'Cluster updated successfully');
                        loadClusters();
                        bootstrap.Modal.getInstance(document.getElementById('editClusterModal')).hide();
                    } else {
                        showResponseMessage('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showResponseMessage('error', 'Failed to update cluster. Please try again.');
                })
                .finally(() => {
                    spinner.classList.add('d-none');
                });
            }
            
            // Function to toggle cluster status
            function toggleClusterStatus(clusterId, newStatus) {
                fetch('api/cluster_api.php', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cluster_id: clusterId,
                        status: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showResponseMessage('success', `Cluster status changed to ${newStatus}`);
                        loadClusters();
                    } else {
                        showResponseMessage('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showResponseMessage('error', 'Failed to update cluster status. Please try again.');
                });
            }
            
            // Function to show delete confirmation modal
            function showDeleteConfirmationModal(clusterId, clusterName) {
                document.getElementById('deleteClusterIdText').textContent = clusterId;
                document.getElementById('deleteClusterNameText').textContent = clusterName;
                
                const modal = new bootstrap.Modal(document.getElementById('deleteClusterModal'));
                modal.show();
            }
            
            // Function to delete cluster
            function deleteCluster() {
                const clusterId = document.getElementById('deleteClusterIdText').textContent;
                
                const spinner = document.querySelector('#confirmDeleteCluster .loading-spinner');
                spinner.classList.remove('d-none');
                
                fetch('api/cluster_api.php', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        cluster_id: clusterId
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        showResponseMessage('success', data.message);
                        loadClusters();
                        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteClusterModal'));
                        modal.hide();
                    } else {
                        showResponseMessage('error', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showResponseMessage('error', 'Failed to delete cluster. Please try again.');
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
                                <p class="mt-2">Loading clusters...</p>
                            </td>
                        </tr>
                    `;
                }
            }
        });
    </script>
</body>
</html>