<div class="modal fade" id="viewUserModal" tabindex="-1" role="dialog" aria-labelledby="viewUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="viewUserForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewUserModalLabel">View User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                   
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Form fields for editing user details -->
                    <input type="hidden" name="id" id="view_user_id">
                    <div class="form-group">
                        <label for="view_firtsname">First Name</label>
                        <input type="text" class="form-control" id="view_firtsname" name="first_name" disabled>
                    </div>
                    <div class="form-group">
                        <label for="view_lastname">Last Name</label>
                        <input type="text" class="form-control capitalize" id="view_lastname" name="last_name" aria-describedby="" disabled>
                        <div class="invalid-feedback" id="lastNameError"></div>
                    </div>
                    <div class="form-group">
                        <label for="view_middlesname">Middle Name</label>
                        <input type="text" class="form-control capitalize" id="view_middlesname" name="middle_name" disabled>
                        <div class="invalid-feedback" id="middleNameError"></div>
                    </div>
                    <div class="form-group">
                        <label for="view_suffix">Suffix</label>
                        <input type="text" class="form-control capitalize" id="view_suffix" name="suffix" aria-describedby="" disabled>
                    </div>
                    <div class="form-group">
                        <label for="view_email">Email</label>
                        <input type="email" class="form-control" id="view_email" name="email" disabled>
                    </div>

                    <div class="form-group">
                        <label for="view_contactNumber">Mobile Number</label>
                        <input type="text" class="form-control" id="view_contactNumber" name="mobile_number" disabled>
                        <div class="invalid-feedback" id="contactNumberError"></div>
                    </div>

                    <div class="form-group">
                        <label for="view_position">Position</label>
                        <input type="text" class="form-control" id="view_position" name="position" disabled>
                    </div>
                    <div class="form-group">
                        <label for="view_province">Province</label>
                        <select class="form-control" id="view_province" name="province" disabled>
                            <option value="">Select a Province...</option>
                            <option value="Albay">Albay</option>
                            <option value="Camarines Norte">Camarines Norte</option>
                            <option value="Camarines Sur">Camarines Sur</option>
                            <option value="Catanduanes">Catanduanes</option>
                            <option value="Masbate">Masbate</option>
                            <option value="Sorsogon">Sorsogon</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="view_role">Role</label>
                        <select class="form-control" id="view_role" name="role" disabled>
                            <!-- Options populated dynamically via AJAX -->
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>