<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editUserForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                   
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Form fields for editing user details -->
                    <input type="hidden" name="id" id="edit_user_id">
                    <div class="form-group">
                        <label for="edit_firtsname">First Name</label>
                        <input type="text" class="form-control" id="edit_firtsname" name="first_name" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_lastname">Last Name</label>
                        <input type="text" class="form-control capitalize" id="edit_lastname" name="last_name" aria-describedby="">
                        <div class="invalid-feedback" id="lastNameError"></div>
                    </div>
                    <div class="form-group">
                        <label for="edit_middlesname">Middle Name</label>
                        <input type="text" class="form-control capitalize" id="edit_middlesname" name="middle_name">
                        <div class="invalid-feedback" id="middleNameError"></div>
                    </div>
                    <div class="form-group">
                        <label for="edit_suffix">Suffix</label>
                        <input type="text" class="form-control capitalize" id="edit_suffix" name="suffix" aria-describedby="">
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_contactNumber">Mobile Number</label>
                        <input type="text" class="form-control" id="edit_contactNumber" name="mobile_number">
                        <div class="invalid-feedback" id="contactNumberError"></div>
                    </div>

                    <div class="form-group">
                        <label for="edit_position">Position</label>
                        <input type="text" class="form-control" id="edit_position" name="position">
                    </div>
                    <div class="form-group">
                        <label for="edit_province">Province</label>
                        <select class="form-control" id="edit_province" name="province">
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
                        <label for="edit_role">Role</label>
                        <select class="form-control" id="edit_role" name="role">
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