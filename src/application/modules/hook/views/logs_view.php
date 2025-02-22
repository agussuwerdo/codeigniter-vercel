<!DOCTYPE html>
<html>
<head>
    <title>Request Logs</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        #loading {
            text-align: center;
            padding: 20px;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-radius: 50%;
            border-top: 4px solid #3498db;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }
        .pagination button {
            padding: 8px 12px;
            border: 1px solid #ddd;
            background: #fff;
            cursor: pointer;
        }
        .pagination button:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }
        .pagination button.active {
            background: #3498db;
            color: white;
            border-color: #3498db;
        }
        .page-info {
            text-align: center;
            color: #666;
            margin: 10px 0;
        }
        .actions {
            margin: 20px 0;
            text-align: right;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .delete-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .delete-btn:hover {
            background-color: #c82333;
        }

        .delete-btn:disabled {
            background-color: #dc354580;
            cursor: not-allowed;
        }
        
        .delete-btn .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff80;
            border-top: 2px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: none;
        }

        .delete-btn.loading .spinner {
            display: inline-block;
        }

        .delete-btn.loading .btn-text {
            opacity: 0.8;
        }
        
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        
        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 5px;
            width: 80%;
            max-width: 500px;
        }
        
        .modal-actions {
            margin-top: 20px;
            text-align: right;
        }
        
        .modal-actions button {
            padding: 8px 16px;
            margin-left: 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .confirm-btn {
            background-color: #dc3545;
            color: white;
            border: none;
        }
        
        .cancel-btn {
            background-color: #6c757d;
            color: white;
            border: none;
        }
        
        .refresh-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .refresh-btn:hover {
            background-color: #218838;
        }

        .refresh-btn:disabled {
            background-color: #28a74580;
            cursor: not-allowed;
        }
        
        .refresh-btn .spinner {
            width: 16px;
            height: 16px;
            border: 2px solid #ffffff80;
            border-top: 2px solid #fff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: none;
        }

        .refresh-btn.loading .spinner {
            display: inline-block;
        }

        .refresh-btn.loading .btn-text {
            opacity: 0.8;
        }
        
        td pre {
            max-height: 200px;
            overflow-y: auto;
            background-color: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #eee;
        }
        
        table th:last-child,
        table td:last-child {
            max-width: 400px;
            width: 30%;
        }
    </style>
</head>
<body>
    <h1>Request Logs</h1>
    
    <div class="actions">
        <button id="refreshBtn" class="refresh-btn" onclick="refreshLogs()">
            <span class="btn-text">Refresh Logs</span>
        </button>
        <button id="deleteBtn" class="delete-btn" onclick="showDeleteConfirmation()">
            <span class="btn-text">Delete All Logs</span>
        </button>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h2>⚠️ Warning</h2>
            <p>Are you sure you want to delete all logs? This action cannot be undone.</p>
            <div class="modal-actions">
                <button id="cancelBtn" class="cancel-btn" onclick="hideDeleteConfirmation()">Cancel</button>
                <button id="confirmBtn" class="delete-btn" onclick="clearAllLogs()">
                    <span class="btn-text">Delete All</span>
                </button>
            </div>
        </div>
    </div>

    <div id="loading">
        <div class="spinner"></div>
        <p>Loading logs...</p>
    </div>
    <table id="logsTable" style="display: none;">
        <thead>
            <tr>
                <th>Timestamp</th>
                <th>Method</th>
                <th>URI</th>
                <th>Query</th>
                <th>Status</th>
                <th>IP</th>
                <th>Response Time (ms)</th>
                <th>Payload</th>
                <th>Response</th>
            </tr>
        </thead>
        <tbody id="logsBody">
        </tbody>
    </table>
    <div id="pagination" class="pagination" style="display: none;"></div>
    <div id="pageInfo" class="page-info" style="display: none;"></div>

    <script>
        let currentPage = 1;
        const perPage = 10;

        async function fetchLogs(page = 1) {
            try {
                document.getElementById('loading').style.display = 'block';
                document.getElementById('logsTable').style.display = 'none';
                document.getElementById('pagination').style.display = 'none';
                document.getElementById('pageInfo').style.display = 'none';

                const response = await fetch(`/hook/get_logs?page=${page}&limit=${perPage}`);
                const data = await response.json();
                
                const logsBody = document.getElementById('logsBody');
                const logsTable = document.getElementById('logsTable');
                const loading = document.getElementById('loading');
                const pagination = document.getElementById('pagination');
                const pageInfo = document.getElementById('pageInfo');

                if (data.logs && data.logs.length > 0) {
                    const rows = data.logs.map(log => {
                        // Function to format base64 encoded content
                        const formatContent = (content) => {
                            if (!content) return '';
                            
                            try {
                                // Decode base64
                                let decoded = atob(content);
                                
                                // If the content contains \n, format it as preformatted text
                                if (decoded.includes('\n')) {
                                    return `<pre style="white-space: pre-wrap; margin: 0; font-family: monospace;">${decoded}</pre>`;
                                }
                                
                                // For other content, just return the decoded text
                                return decoded;
                            } catch (e) {
                                // If decoding fails, return the original content
                                return content;
                            }
                        };

                        return `
                            <tr>
                                <td>${log.timestamp || ''}</td>
                                <td>${log.method || ''}</td>
                                <td>${log.uri || ''}</td>
                                <td>${log.query || ''}</td>
                                <td>${log.status_code || ''}</td>
                                <td>${log.ip || ''}</td>
                                <td>${log.response_time || ''}</td>
                                <td>${formatContent(log.payload)}</td>
                                <td>${formatContent(log.response)}</td>
                            </tr>
                        `;
                    }).join('');
                    
                    logsBody.innerHTML = rows;
                    
                    // Update pagination
                    const totalPages = data.pagination.total_pages;
                    let paginationHtml = `
                        <button onclick="fetchLogs(1)" ${page === 1 ? 'disabled' : ''}>First</button>
                        <button onclick="fetchLogs(${page - 1})" ${page === 1 ? 'disabled' : ''}>Previous</button>
                    `;
                    
                    // Add page numbers
                    for (let i = Math.max(1, page - 2); i <= Math.min(totalPages, page + 2); i++) {
                        paginationHtml += `
                            <button onclick="fetchLogs(${i})" class="${i === page ? 'active' : ''}">${i}</button>
                        `;
                    }
                    
                    paginationHtml += `
                        <button onclick="fetchLogs(${page + 1})" ${page === totalPages ? 'disabled' : ''}>Next</button>
                        <button onclick="fetchLogs(${totalPages})" ${page === totalPages ? 'disabled' : ''}>Last</button>
                    `;
                    
                    pagination.innerHTML = paginationHtml;
                    pageInfo.innerHTML = `Showing ${(page - 1) * perPage + 1} to ${Math.min(page * perPage, data.pagination.total)} of ${data.pagination.total} entries`;
                } else {
                    logsBody.innerHTML = '<tr><td colspan="7">No logs found</td></tr>';
                    pagination.innerHTML = '';
                    pageInfo.innerHTML = '';
                }

                loading.style.display = 'none';
                logsTable.style.display = 'table';
                pagination.style.display = 'flex';
                pageInfo.style.display = 'block';
                
                // Update current page
                currentPage = page;
            } catch (error) {
                console.error('Error fetching logs:', error);
                document.getElementById('loading').innerHTML = 
                    '<p style="color: red;">Error loading logs. Please try again later.</p>';
            }
        }

        // Fetch logs when page loads
        fetchLogs();

        function showDeleteConfirmation() {
            document.getElementById('deleteModal').style.display = 'block';
        }

        function hideDeleteConfirmation() {
            document.getElementById('deleteModal').style.display = 'none';
            // Reset button states
            const confirmBtn = document.getElementById('confirmBtn');
            confirmBtn.disabled = false;
            confirmBtn.classList.remove('loading');
        }

        async function clearAllLogs() {
            const confirmBtn = document.getElementById('confirmBtn');
            const cancelBtn = document.getElementById('cancelBtn');

            try {
                // Disable buttons and show loading
                confirmBtn.disabled = true;
                cancelBtn.disabled = true;
                confirmBtn.classList.add('loading');

                const response = await fetch('/hook/clear_logs', {
                    method: 'POST'
                });
                const data = await response.json();
                
                if (data.success) {
                    // Hide modal and reset buttons
                    hideDeleteConfirmation();
                    // Refresh logs
                    fetchLogs();
                } else {
                    throw new Error(data.error || 'Failed to clear logs');
                }
            } catch (error) {
                console.error('Error clearing logs:', error);
                alert(error.message || 'Failed to clear logs. Please try again.');
            } finally {
                // Re-enable buttons and hide loading
                confirmBtn.disabled = false;
                cancelBtn.disabled = false;
                confirmBtn.classList.remove('loading');
            }
        }

        // Close modal if clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('deleteModal');
            if (event.target === modal) {
                hideDeleteConfirmation();
            }
        }

        async function refreshLogs() {
            const refreshBtn = document.getElementById('refreshBtn');
            
            try {
                // Disable button and show loading
                refreshBtn.disabled = true;
                refreshBtn.classList.add('loading');
                
                // Fetch logs for current page
                await fetchLogs(currentPage);
            } catch (error) {
                console.error('Error refreshing logs:', error);
                alert('Failed to refresh logs. Please try again.');
            } finally {
                // Re-enable button and hide loading
                refreshBtn.disabled = false;
                refreshBtn.classList.remove('loading');
            }
        }
    </script>
</body>
</html>
