<!-- Core JS -->
    <!-- build:js assets/vendor/js/core.js -->

    <script src={{asset('assets/vendor/libs/jquery/jquery.js')}}></script>
    <script src={{asset('assets/vendor/libs/popper/popper.js')}}></script>
    <script src={{asset('assets/vendor/js/bootstrap.js')}}></script>
    <script src={{asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js')}}></script>
    <script src={{asset('assets/vendor/libs/hammer/hammer.js')}}></script>
    <script src={{asset('assets/vendor/libs/flatpickr/flatpickr.js')}}></script>
    <script src={{asset('assets/vendor/libs/sweetalert2/sweetalert2.js')}}></script>
    <script src={{asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js')}}></script>
    <script src={{asset('assets/vendor/js/menu.js')}}></script>
    <script src={{asset('assets/vendor/libs/select2/select2.js')}}></script>


    <!-- CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>

    <!-- endbuild -->

    <!-- Vendors JS -->

    <!-- Main JS -->
    <script src={{asset('assets/js/main.js')}}></script>

    {{-- EMAIL DATATABLES START --}}
    <script>
         const user = @json(Auth::user());
         console.log(user);

        $(function () {
            var data = [
                { name: '<small class="text-success">To:&nbsp;&nbsp;</small>John Doe', subject: 'Budget Office Acknowledge the Transaction', date: 'Jul 5' },
                { name: '<small class="text-success">To:&nbsp;&nbsp;</small>John Doe Jr.', subject: 'Faculty 1 received his Honorarium', date: 'Jul 5' },
                { name: '<small class="text-success">To:&nbsp;&nbsp;</small>John Doe Papa Dot', subject: 'Thank you for the info. will be there', date: 'Jul 5' },
                { name: '<small class="text-success">To:&nbsp;&nbsp;</small>John Doe Papa Dot Jr.', subject: 'Transaction has proceeded to Accounting', date: 'Jul 5' },
                // Add more objects as needed
            ];

            var table = $('#inboxTable').DataTable({
                data: data,
                processing: false,
                serverSide: false,
                pageLength: 100,
                paging: false, // Disable pagination
                dom: '<"top"f>rt<"bottom"ip>',
                language: {
                    search: "", // Remove the default search label
                    searchPlaceholder: "Search..." // Set the placeholder text
                },
                columns: [
                    {
                        data: null,
                        orderable: false,
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="form-check-input row-checkbox">';
                        }
                    },
                    { data: 'name', name: 'name', title: 'Name' },
                    { data: 'subject', name: 'subject', title: 'Subject' },
                    { data: 'date', name: 'date', title: 'Date' }
                ],
                createdRow: function(row, data) {
                    // Add class to unopened rows
                    $(row).addClass('unopened');
                }
            });

            // Function to update the Toggle Check button text
            function updateToggleCheckButton() {
                var allChecked = $('#inboxTable tbody input.row-checkbox').length === $('#inboxTable tbody input.row-checkbox:checked').length;
                var anyChecked = $('#inboxTable tbody input.row-checkbox:checked').length > 0;

                if (allChecked || anyChecked) {
                    $('#toggleCheck').text('Uncheck');
                } else {
                    $('#toggleCheck').text('Check All');
                }
            }

            // Initial call to set the button text based on initial state
            updateToggleCheckButton();

            // Prevent checkbox click from triggering row click event
            $('#inboxTable tbody').on('click', 'input.row-checkbox', function(e) {
                e.stopPropagation();
                updateToggleCheckButton(); // Update button text when a checkbox is clicked
            });

            // Row click event
            $('#inboxTable tbody').on('click', 'tr', function() {
                var rowData = table.row($(this).closest('tr')).data();

                // If the row is unopened, change its class to opened
                if ($(this).hasClass('unopened')) {
                    $(this).removeClass('unopened').addClass('opened');
                }

                // Redirect to another page with full details (example)
                window.location.href = `/admin_open_email?id=${rowData.id}`;
            });

            // Toggle Check All/Uncheck All button click event
            $('#toggleCheck').on('click', function() {
                var allChecked = $('#inboxTable tbody input.row-checkbox').length === $('#inboxTable tbody input.row-checkbox:checked').length;

                if (allChecked) {
                    $('#inboxTable tbody input.row-checkbox').prop('checked', false);
                    $('#toggleCheck').text('Check All');
                } else {
                    $('#inboxTable tbody input.row-checkbox').prop('checked', true);
                    $('#toggleCheck').text('Uncheck');
                }
            });

            // Delete Selected button click event
            $('#deleteSelected').on('click', function() {
                $('#inboxTable tbody input.row-checkbox:checked').each(function() {
                    var row = $(this).closest('tr');
                    table.row(row).remove().draw(false);
                });
                updateToggleCheckButton(); // Update button text after deletion
            });

            // Delete row button click event
            $('#inboxTable tbody').on('click', '.delete-row', function(e) {
                e.stopPropagation();
                var row = $(this).closest('tr');
                table.row(row).remove().draw(false);
                updateToggleCheckButton(); // Update button text after deletion
            });
        });
    </script>

{{-- EMAIL DATATABLES END --}}



{{-- SENT ITEMS DATATABLES START --}}
<script>

</script>
{{-- SENT-ITEMS DATATABLES END --}}



{{-- BACK NAVIGATION BUTTON START --}}
<script>
    $(document).ready(function() {
        $('#navigatePrevious').on('click', function() {
            window.history.back();
        });
    });
</script>
{{-- BACK NAVIGATION BUTTON START --}}




