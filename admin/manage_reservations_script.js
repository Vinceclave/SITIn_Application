<script>
    document.addEventListener('DOMContentLoaded', function() {
        const acceptButtons = document.querySelectorAll('.accept-btn');
        const rejectButtons = document.querySelectorAll('.reject-btn');

        acceptButtons.forEach(button => {
            button.addEventListener('click', function() {
                const reservationId = this.dataset.id;
                updateReservationStatus(reservationId, 'approved');
            });
        });

        rejectButtons.forEach(button => {
            button.addEventListener('click', function() {
                const reservationId = this.dataset.id;
                updateReservationStatus(reservationId, 'rejected');
            });
        });

        function updateReservationStatus(reservationId, status) {
            // Disable buttons to prevent multiple clicks
            const buttons = document.querySelectorAll(`.accept-btn[data-id="${reservationId}"], .reject-btn[data-id="${reservationId}"]`);
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.classList.add('opacity-50', 'cursor-not-allowed');
            });

            fetch('update_reservation_status.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `reservation_id=${reservationId}&status=${status}`,
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Show success notification
                    const notificationDiv = document.createElement('div');
                    notificationDiv.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded z-50';
                    notificationDiv.innerHTML = `<strong>Success!</strong> ${status === 'approved' ? 'Reservation approved.' : 'Reservation rejected.'}`;
                    document.body.appendChild(notificationDiv);
                    
                    // Remove notification after 3 seconds
                    setTimeout(() => {
                        notificationDiv.remove();
                    }, 3000);

                    const row = document.querySelector(`tr[data-id="${reservationId}"]`);
                    const statusCell = row.querySelector('td:nth-child(9) span');
                    const actionsCell = row.querySelector('td:last-child');

                    statusCell.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                    statusCell.classList.remove('bg-yellow-100', 'text-yellow-800', 'bg-green-100', 'text-green-800', 'bg-red-100', 'text-red-800', 'bg-gray-100','text-gray-800');
                    if (status === 'approved') {
                        statusCell.classList.add('bg-green-100', 'text-green-800');
                    } else if (status === 'rejected') {
                        statusCell.classList.add('bg-red-100', 'text-red-800');
                    } else if (status === 'completed') {
                        statusCell.classList.add('bg-gray-100', 'text-gray-800');
                    } else {
                        statusCell.classList.add('bg-yellow-100', 'text-yellow-800');
                    }
                    
                    actionsCell.innerHTML = ''; 
                } else {
                    // Show error notification
                    const notificationDiv = document.createElement('div');
                    notificationDiv.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50';
                    notificationDiv.innerHTML = `<strong>Error!</strong> ${data.message || 'Error updating reservation status.'}`;
                    document.body.appendChild(notificationDiv);
                    
                    // Remove notification after 3 seconds
                    setTimeout(() => {
                        notificationDiv.remove();
                    }, 3000);

                    // Re-enable the buttons
                    buttons.forEach(btn => {
                        btn.disabled = false;
                        btn.classList.remove('opacity-50', 'cursor-not-allowed');
                    });

                    console.error('Error updating reservation status:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                // Show error notification
                const notificationDiv = document.createElement('div');
                notificationDiv.className = 'fixed top-4 right-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded z-50';
                notificationDiv.innerHTML = `<strong>Error!</strong> Network error. Please try again.`;
                document.body.appendChild(notificationDiv);
                
                // Remove notification after 3 seconds
                setTimeout(() => {
                    notificationDiv.remove();
                }, 3000);

                // Re-enable the buttons
                buttons.forEach(btn => {
                    btn.disabled = false;
                    btn.classList.remove('opacity-50', 'cursor-not-allowed');
                });
            });
        }
    });
</script>
