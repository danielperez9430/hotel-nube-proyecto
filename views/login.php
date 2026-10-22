<!-- Sección de login de la página -->
<div class="modal fade" tabindex="-1" id="loginDialog" aria-labelledby="logRegTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logRegTitle">Login</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="loginNotice" class="alert alert-info" style="display: none;"></div>
                <div class="loginContent" id="loginContent">
                    <div class="hn-dialog-intro">
                        <img src="src/logo/hotel_nube_sin_fondo.svg" alt="" height="48">
                        <p>Welcome back. Log in to book and manage your stays.</p>
                    </div>
                    <div class="form-floating">
                        <input type="email" class="form-control" id="loginEmail" placeholder="Email" autocomplete="email">
                        <label for="loginEmail">Email</label>
                    </div>
                    <div class="form-floating">
                        <input type="password" class="form-control" id="loginPassword" placeholder="Password" autocomplete="current-password">
                        <label for="loginPassword">Password</label>
                    </div>
                    <div class="buttons">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="keepLogged">
                            <label class="form-check-label" for="keepLogged">Keep logged in</label>
                        </div>
                        <button id="loginBtn" class="btn btn-primary" disabled>Login</button>
                    </div>
                    <div class="goTo">
                        <button type="button" class="hn-link" id="goForgot">Forgot your password?</button>
                        <button type="button" class="hn-link" id="goRegister">Not registered yet?</button>
                    </div>
                </div>
                <div class="forgotContent" id="forgotContent" style="display: none;">
                    <p class="mb-0">Enter the email of your account and we will send you a link to choose a new password.</p>
                    <div class="form-floating">
                        <input type="email" class="form-control" id="forgotEmail" placeholder="Email" autocomplete="email">
                        <label for="forgotEmail">Email</label>
                    </div>
                    <div id="forgotMessage" class="alert mb-0" style="display: none;"></div>
                    <div class="buttons">
                        <button id="forgotBtn" class="btn btn-primary" disabled>Send link</button>
                    </div>
                    <div class="goTo">
                        <button type="button" class="hn-link goLoginLink">Back to login</button>
                    </div>
                </div>
                <div class="registerContent" id="registerContent" style="display: none;">
                    <div class="registerInputs">
                        <div class="title"><span>Account data</span></div>
                        <div class="form-floating">
                            <input type="email" class="form-control" id="registerEmail" placeholder="Email" autocomplete="email" required>
                            <label for="registerEmail">Email</label>
                        </div>
                        <div class="form-floating">
                            <input type="password" class="form-control" id="registerPassword" placeholder="Password" autocomplete="new-password" minlength="8" maxlength="72" required>
                            <label for="registerPassword">Password (min. 8 characters)</label>
                        </div>
                    </div>
                    <div class="registerInputs">
                        <div class="title"><span>Personal data</span></div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="registerName" placeholder="Name" autocomplete="name" required>
                            <label for="registerName">Name</label>
                        </div>
                        <div class="phone">
                            <div class="form-floating phone-code">
                                <input type="text" class="form-control" id="registerPhoneCode" placeholder="+34" autocomplete="tel-country-code" maxlength="4" required>
                                <label for="registerPhoneCode">Code</label>
                            </div>
                            <div class="form-floating phone-number">
                                <input type="text" class="form-control" id="registerPhoneNumber" placeholder="612345678" autocomplete="tel-national" maxlength="9" required>
                                <label for="registerPhoneNumber">Phone number</label>
                            </div>
                        </div>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="registerAddress" placeholder="Address" autocomplete="street-address" maxlength="60" required>
                            <label for="registerAddress">Address</label>
                        </div>
                        <div class="form-floating">
                            <select class="form-select" id="registerCountry">
                                <option value="loading">Loading...</option>
                            </select>
                            <label for="registerCountry">Country</label>
                        </div>
                        <div class="form-floating">
                            <select class="form-select" id="registerProvince" disabled></select>
                            <label for="registerProvince">Province</label>
                        </div>
                        <div class="form-floating">
                            <select class="form-select" id="registerCity" disabled></select>
                            <label for="registerCity">City</label>
                        </div>
                    </div>
                    <div class="buttons">
                        <button id="registerBtn" class="btn btn-primary" disabled>Register</button>
                    </div>
                    <div class="goTo">
                        <button type="button" class="hn-link goLoginLink" id="goLogin">Already registered?</button>
                    </div>
                </div>
            </div>
            <div id="spinnerContainer" class="login-overlay" style="display: none">
                <div class="spinner-border" role="status"></div>
                <span>Logging in...</span>
            </div>
            <div id="loginFail" class="login-overlay" style="display: none">
                <i class="fas fa-times-circle"></i>
                <span>Login failed, retry again.</span>
            </div>
            <div id="loginSuccessful" class="login-overlay" style="display: none">
                <i class="fas fa-check-circle"></i>
                <span>Login successful.</span>
            </div>
        </div>
    </div>
</div>
