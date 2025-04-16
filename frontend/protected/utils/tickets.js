document.addEventListener('DOMContentLoaded', function() {
    console.log('Tickets.js script loaded successfully');
    
    /*
    =========================================
    TICKETS PAGE FUNCTIONALITY
    ========================================= 
    */
    // Ticket status filter
    const statusFilters = document.querySelectorAll('.status-filter');
    const ticketsSearchBox = document.getElementById('ticketsSearch');
    
    console.log('Filter buttons found:', statusFilters.length);
    
    // Function to update ticket visibility based on status and search
    function updateTicketVisibility() {
        const activeFilter = document.querySelector('.status-filter.active');
        const statusFilter = activeFilter ? activeFilter.getAttribute('data-status') : 'all';
        const searchTerm = ticketsSearchBox ? ticketsSearchBox.value.toLowerCase() : '';
        
        console.log('Filtering by status:', statusFilter);
        
        const rows = document.querySelectorAll('.tickets-table tbody tr');
        console.log('Found rows to filter:', rows.length);
        
        let visibleCount = 0;
        
        rows.forEach(row => {
            // Skip header row if it somehow got selected
            if (row.parentElement.tagName === 'THEAD') return;
            
            // Skip "no tickets found" row
            if (row.cells && row.cells.length === 1 && row.cells[0].colSpan === 7) return;
            
            const rowStatus = row.getAttribute('data-status') || '';
            const rowText = row.textContent.toLowerCase();
            
            let showRow = true;
            
            // Filter by status if not 'all'
            if (statusFilter !== 'all' && rowStatus !== statusFilter) {
                // Special handling for "in behandeling" which might be stored as "in-progress" in some rows
                if (!(statusFilter === 'in behandeling' && (rowStatus === 'in-progress' || rowStatus === 'in behandeling'))) {
                    showRow = false;
                }
            }
            
            // Filter by search term if present
            if (searchTerm && !rowText.includes(searchTerm)) {
                showRow = false;
            }
            
            row.style.display = showRow ? '' : 'none';
            if (showRow) visibleCount++;
            
            console.log('Row:', row.querySelector('td:first-child')?.textContent, 'Status:', rowStatus, 'Visible:', showRow);
        });
        
        console.log('Visible rows after filtering:', visibleCount);
    }
    
    // Set up status filters - adding event listeners
    if (statusFilters.length > 0) {
        statusFilters.forEach(filter => {
            const status = filter.getAttribute('data-status');
            console.log('Setting up filter for status:', status);
            
            filter.addEventListener('click', function() {
                console.log('Filter clicked:', status);
                
                // Remove active class from all filters
                statusFilters.forEach(f => f.classList.remove('active'));
                
                // Add active class to clicked filter
                this.classList.add('active');
                
                // Update visibility
                updateTicketVisibility();
            });
        });
    }
    
    // Set up search box
    if (ticketsSearchBox) {
        console.log('Search box found, setting up event listener');
        ticketsSearchBox.addEventListener('input', function() {
            console.log('Search term:', this.value);
            updateTicketVisibility();
        });
    }
    
    // We don't need to handle view buttons here anymore as they're now regular links
    
    // Initialize filtering
    console.log('Initializing filtering');
    updateTicketVisibility();
});
