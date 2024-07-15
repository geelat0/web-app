<div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserModalLabel">Create New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
                   
                </button>
            </div>
            <div class="modal-body">
                <form id="createUserForm">
                    @csrf
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" class="form-control capitalize" name="first_name" id="firstName" aria-describedby="">
                        <div class="invalid-feedback" id="firstNameError"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" class="form-control capitalize" id="lastName" name="last_name" aria-describedby="">
                        <div class="invalid-feedback" id="lastNameError"></div>
                    </div>
                    <div class="form-group">
                        <label for="middleName">Middle Name</label>
                        <input type="text" class="form-control capitalize" id="middleName" name="middle_name">
                        <div class="invalid-feedback" id="middleNameError"></div>
                    </div>
                    <div class="form-group">
                        <label for="suffix">Suffix</label>
                        <input type="text" class="form-control capitalize" id="suffix" name="suffix" aria-describedby="">
                    </div>
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="text" class="form-control" id="email" aria-describedby="" name="email">
                        <div class="invalid-feedback" id="emailError"></div>
                    </div>

                    <div class="form-group">
                        <label for="contactNumber">Mobile Number</label>
                        <input type="text" class="form-control" id="contactNumber" name="mobile_number">
                        <div class="invalid-feedback" id="contactNumberError"></div>
                    </div>

                    <div class="form-group">
                        <label for="position">Position</label>
                        <input type="text" class="form-control capitalize" id="position" name="position" aria-describedby="">
                        <div class="invalid-feedback" id="positionError"></div>
                    </div>
                    <div class="form-group">
                        <label for="province">Province</label>
                        <select class="form-control" id="province" name="province">
                            <option value="">Select a Province...</option>
                            <option value="Albay">Albay</option>
                            <option value="Camarines Norte">Camarines Norte</option>
                            <option value="Camarines Sur">Camarines Sur</option>
                            <option value="Catanduanes">Catanduanes</option>
                            <option value="Masbate">Masbate</option>
                            <option value="Sorsogon">Sorsogon</option>
                        </select>
                        <div class="invalid-feedback" id="collegeError"></div>
                    </div>
                   
                    <div class="form-group">
                        <label for="rolesDropdown">Role</label>
                        <select id="rolesDropdown" class="form-control" name="role_id">
                            <option value="">Select a Role...</option>
                        </select>
                        <div class="invalid-feedback" id="RoleError"></div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" class="form-control" id="password" aria-describedby="" name="password" placeholder="Password">
                        <div class="invalid-feedback" id="passwordError"></div>
                    </div>

                    <div class="form-group">
                        <label for="confirmPassword">Confirm Password</label>
                        <input type="password" class="form-control" id="confirmPassword" aria-describedby="" name="password" placeholder="Confirm Password">
                        <div class="invalid-feedback" id="confirmPasswordError"></div>
                    </div>

                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</div>

