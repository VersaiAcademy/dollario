<?php  include '../templates/sidebar.php'; include '../templates/header.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KYC Management | Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0C8773;
            --card-bg: #ffffff;
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-color: #2d2d2d;
        }

        /* Dark mode variables */
        .dark-mode {
            --card-bg: #1f2937;
            --text-color: #f9fafb;
            --glass-border: rgba(31, 41, 55, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f3f4f6;
            color: var(--text-color);
            transition: background 0.3s ease;
        }

        /* Table Styles */
        .data-table-container {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 1rem;
            margin: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-left: 260px;
        }

        #kycTable {
            width: 100%;
            border-collapse: collapse;
        }

        #kycTable th, #kycTable td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        #kycTable tr:hover {
            background: rgba(0, 0, 0, 0.03);
        }

        /* Responsive Table */
        @media (max-width: 768px) {
            #kycTable thead {
                display: none;
            }

            #kycTable tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
            }

            #kycTable td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 0.5rem;
                text-align: right;
            }

            #kycTable td::before {
                content: attr(data-label);
                font-weight: 500;
                margin-right: 1rem;
                text-align: left;
            }
            .header{
                margin-left: 0px;
            }
            .data-table-container{
                margin-left: 0px;
            }
        }

        /* Status Badges */
        .kyc-status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-pending {
            background: #ffedd5;
            color: #c2410c;
        }

        .status-approved {
            background: #dcfce7;
            color: #166534;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            max-width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Document Preview */
        .document-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }

        .document-card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
        }

        .document-image {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background: #f3f4f6;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="data-table-container">
        <table id="kycTable">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Submitted</th>
                    <th>Documents</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="kycTableBody">
                <!-- Dynamically populated -->
            </tbody>
        </table>
    </div>

    <!-- KYC Review Modal -->
    <div class="modal" id="reviewModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Review KYC</h3>
                <button onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="userDetails"></div>
                <div class="document-grid" id="documentContainer"></div>
                <div class="action-buttons">
                    <button onclick="approveKYC()">Approve</button>
                    <button onclick="rejectKYC()">Reject</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mock Database
        const kycDB = {
            applications: [
                {
                    id: 'KYC001',
                    user: {
                        name: 'John Doe',
                        email: 'john@example.com',
                        joined: '2024-01-15'
                    },
                    submitted: '2024-03-01T14:30:00',
                    documents: {
                        pan: 'pan.jpg',
                        aadhaar: ['aadhaar-front.jpg', 'aadhaar-back.jpg']
                    },
                    status: 'pending',
                    notes: ''
                },
                // Add more entries as needed
            ],

            getPending: () => kycDB.applications.filter(app => app.status === 'pending'),
            updateStatus: (id, status, notes) => {
                const index = kycDB.applications.findIndex(app => app.id === id);
                if (index > -1) {
                    kycDB.applications[index].status = status;
                    kycDB.applications[index].notes = notes;
                    return true;
                }
                return false;
            }
        };

        // UI Functions
        function renderTable() {
            const tbody = document.getElementById('kycTableBody');
            tbody.innerHTML = '';

            kycDB.getPending().forEach(app => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td data-label="User">
                        <div class="user-info">
                            <strong>${app.user.name}</strong>
                            <small>${app.user.email}</small>
                        </div>
                    </td>
                    <td data-label="Submitted">
                        ${new Date(app.submitted).toLocaleDateString()}
                    </td>
                    <td data-label="Documents">
                        PAN + Aadhaar
                    </td>
                    <td data-label="Status">
                        <span class="kyc-status-badge status-${app.status}">
                            ${app.status.charAt(0).toUpperCase() + app.status.slice(1)}
                        </span>
                    </td>
                    <td data-label="Actions">
                        <button onclick="openReview('${app.id}')">Review</button>
                    </td>
                `;
                tbody.appendChild(row);
            });
        }

        let currentReviewId = null;

        function openReview(id) {
            currentReviewId = id;
            const app = kycDB.applications.find(a => a.id === id);
            
            document.getElementById('userDetails').innerHTML = `
                <h4>${app.user.name}</h4>
                <p>Member since: ${new Date(app.user.joined).toLocaleDateString()}</p>
            `;

            document.getElementById('documentContainer').innerHTML = `
                <div class="document-card">
                    <h4>PAN Card</h4>
                    <img src="${app.documents.pan}" class="document-image">
                </div>
                <div class="document-card">
                    <h4>Aadhaar Card</h4>
                    ${app.documents.aadhaar.map(img => `
                        <img src="${img}" class="document-image">
                    `).join('')}
                </div>
            `;

            document.getElementById('reviewModal').style.display = 'flex';
        }

        function closeModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }

        function approveKYC() {
            if (kycDB.updateStatus(currentReviewId, 'approved', 'Approved by admin')) {
                renderTable();
                closeModal();
                alert('KYC Approved successfully!');
            }
        }

        function rejectKYC() {
            const notes = prompt('Enter rejection reason:');
            if (notes && kycDB.updateStatus(currentReviewId, 'rejected', notes)) {
                renderTable();
                closeModal();
                alert('KYC Rejected');
            }
        }

        // Initial render
        renderTable();
    </script>
</body>
</html>
<?php include '../templates/footer.php'; ?>