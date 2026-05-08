(function () {
    var birthdayInput = document.querySelector('.js-birthday-picker');
    if (birthdayInput && typeof flatpickr === 'function') {
        flatpickr(birthdayInput, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd/m/Y',
            locale: (flatpickr.l10ns && flatpickr.l10ns.vn) ? flatpickr.l10ns.vn : 'default',
            maxDate: 'today',
            minDate: '1900-01-01',
            monthSelectorType: 'static'
        });
    }

    var input = document.getElementById('account-avatar-input');
    var removeInput = document.getElementById('account-avatar-remove');
    var previewWrap = document.querySelector('.js-avatar-preview');
    var uploader = document.querySelector('.js-account-uploader');
    var editBtn = document.querySelector('.js-avatar-edit');
    var deleteBtn = document.querySelector('.js-avatar-delete');
    var dropzone = document.querySelector('.js-avatar-dropzone');

    if (input && previewWrap && uploader) {
        var defaultSrc = uploader.getAttribute('data-default-src') || '';

        function setPreview(file) {
            if (!file) return;
            var reader = new FileReader();
            reader.onload = function (e) {
                previewWrap.innerHTML = '<img src="' + e.target.result + '" alt="Avatar">';
            };
            reader.readAsDataURL(file);
            if (removeInput) removeInput.value = '0';
        }

        input.addEventListener('change', function () {
            if (input.files && input.files[0]) setPreview(input.files[0]);
        });

        if (editBtn) {
            editBtn.addEventListener('click', function () {
                input.click();
            });
        }

        if (deleteBtn) {
            deleteBtn.addEventListener('click', function () {
                input.value = '';
                if (removeInput) removeInput.value = '1';
                if (defaultSrc !== '') {
                    previewWrap.innerHTML = '<img src="' + defaultSrc + '" alt="No image">';
                }
            });
        }

        if (dropzone) {
            ['dragenter', 'dragover'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('is-dragover');
                });
            });

            ['dragleave', 'drop'].forEach(function (eventName) {
                dropzone.addEventListener(eventName, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('is-dragover');
                });
            });

            dropzone.addEventListener('drop', function (e) {
                var files = e.dataTransfer && e.dataTransfer.files ? e.dataTransfer.files : null;
                if (!files || !files.length) return;
                try {
                    var dt = new DataTransfer();
                    dt.items.add(files[0]);
                    input.files = dt.files;
                } catch (err) {
                    return;
                }
                setPreview(files[0]);
            });
        }
    }

    try {
        var addressPanel = document.querySelector('.account-panel[data-wards-url]');
        if (addressPanel) {
        var wardsUrl = addressPanel.getAttribute('data-wards-url') || '';
        var addressForm = addressPanel.querySelector('.js-account-address-form');
        var addressIdInput = addressPanel.querySelector('.js-address-id');
        var recipientNameInput = addressPanel.querySelector('.js-address-recipient-name');
        var recipientPhoneInput = addressPanel.querySelector('.js-address-recipient-phone');
        var addressLineInput = addressPanel.querySelector('.js-address-line');
        var citySelect = addressPanel.querySelector('.js-account-city');
        var wardSelect = addressPanel.querySelector('.js-account-ward');
        var isDefaultInput = addressPanel.querySelector('.js-address-default');
        var editingIndicator = addressPanel.querySelector('.js-address-editing-indicator');
        var submitBtn = addressPanel.querySelector('.js-address-submit');
        var cancelEditBtn = addressPanel.querySelector('.js-address-cancel-edit');
        var editButtons = addressPanel.querySelectorAll('.js-address-edit');
        var addressCards = addressPanel.querySelectorAll('.js-address-card');
        var hasSelect2 = window.jQuery && window.jQuery.fn && typeof window.jQuery.fn.select2 === 'function';
        var initialFormState = {
            addressId: addressIdInput ? (addressIdInput.value || '') : '',
            recipientName: recipientNameInput ? (recipientNameInput.value || '') : '',
            recipientPhone: recipientPhoneInput ? (recipientPhoneInput.value || '') : '',
            addressLine: addressLineInput ? (addressLineInput.value || '') : '',
            city: citySelect ? (citySelect.value || '') : '',
            isDefault: isDefaultInput ? !!isDefaultInput.checked : false
        };

        if (hasSelect2 && citySelect && wardSelect) {
            window.jQuery(citySelect).select2({
                width: '100%',
                placeholder: 'Ch\u1ecdn t\u1ec9nh/th\u00e0nh',
                allowClear: false,
                minimumResultsForSearch: 0,
                dropdownParent: window.jQuery(addressPanel),
                selectionCssClass: 'account-select2-selection',
                dropdownCssClass: 'account-select2-dropdown'
            });
            window.jQuery(wardSelect).select2({
                width: '100%',
                placeholder: 'Ch\u1ecdn ph\u01b0\u1eddng/x\u00e3',
                allowClear: false,
                minimumResultsForSearch: 0,
                dropdownParent: window.jQuery(addressPanel),
                selectionCssClass: 'account-select2-selection',
                dropdownCssClass: 'account-select2-dropdown'
            });
        }

        function resetWards() {
            if (!wardSelect) return;
            wardSelect.innerHTML = '<option value="">Ch\u1ecdn ph\u01b0\u1eddng/x\u00e3</option>';
            wardSelect.disabled = true;
            if (hasSelect2) {
                window.jQuery(wardSelect).prop('disabled', true).val('').trigger('change.select2');
            }
        }

        function setWards(wards, selectedWard) {
            resetWards();
            if (!wardSelect || !Array.isArray(wards) || wards.length === 0) return;
            wards.forEach(function (ward) {
                var option = document.createElement('option');
                option.value = ward.namevi || '';
                option.textContent = ward.namevi || '';
                wardSelect.appendChild(option);
            });
            wardSelect.disabled = false;
            if (hasSelect2) {
                var nextWard = selectedWard || '';
                window.jQuery(wardSelect).prop('disabled', false).val(nextWard).trigger('change.select2');
                return;
            }
            wardSelect.value = selectedWard || '';
        }

        function loadWardsByCity(cityId, selectedWard) {
            if (!wardsUrl || !cityId) {
                resetWards();
                return;
            }
            fetch(wardsUrl + '?city_id=' + encodeURIComponent(cityId), { credentials: 'same-origin' })
                .then(function (response) {
                    return response.ok ? response.json() : { wards: [] };
                })
                .then(function (payload) {
                    setWards(payload && payload.wards ? payload.wards : [], selectedWard || '');
                })
                .catch(function () {
                    resetWards();
                });
        }

        function setCityValue(value) {
            if (!citySelect) return;
            if (hasSelect2) {
                window.jQuery(citySelect).val(value || '').trigger('change.select2');
                return;
            }
            citySelect.value = value || '';
        }

        function resolveCityOptionValue(cityName) {
            if (!citySelect || !cityName) return '';
            var keyword = cityName.trim().toLowerCase();
            var options = citySelect.options || [];
            for (var i = 0; i < options.length; i++) {
                var option = options[i];
                var optionCityName = (option.getAttribute('data-city-name') || '').trim().toLowerCase();
                if (optionCityName !== '' && optionCityName === keyword) {
                    return option.value || '';
                }
            }
            return '';
        }

        function toggleEditState(isEditing) {
            if (submitBtn) {
                submitBtn.textContent = isEditing ? '\u004c\u01b0u ch\u1ec9nh s\u1eeda' : '+ Th\u00eam \u0111\u1ecba ch\u1ec9 m\u1edbi';
            }
            if (cancelEditBtn) {
                cancelEditBtn.style.display = isEditing ? '' : 'none';
            }
        }

        function setEditingIndicator(isEditing, label) {
            if (!editingIndicator) return;
            if (!isEditing) {
                editingIndicator.style.display = 'none';
                editingIndicator.textContent = '';
                return;
            }
            editingIndicator.style.display = '';
            editingIndicator.textContent = label
                ? ('\u0110ang ch\u1ec9nh s\u1eeda: ' + label)
                : '\u0110ang ch\u1ec9nh s\u1eeda \u0111\u1ecba ch\u1ec9.';
        }

        function markEditingCard(button) {
            if (addressCards && addressCards.length) {
                Array.prototype.forEach.call(addressCards, function (card) {
                    card.classList.remove('is-editing');
                });
            }
            if (!button) return;
            var card = button.closest('.js-address-card');
            if (card) {
                card.classList.add('is-editing');
            }
        }

        function resetAddressForm() {
            if (addressIdInput) addressIdInput.value = initialFormState.addressId;
            if (recipientNameInput) recipientNameInput.value = initialFormState.recipientName;
            if (recipientPhoneInput) recipientPhoneInput.value = initialFormState.recipientPhone;
            if (addressLineInput) addressLineInput.value = initialFormState.addressLine;
            if (isDefaultInput) isDefaultInput.checked = initialFormState.isDefault;
            setCityValue(initialFormState.city || '');
            if (initialFormState.city) {
                loadWardsByCity(initialFormState.city, '');
            } else {
                resetWards();
            }
            toggleEditState(false);
            setEditingIndicator(false, '');
            markEditingCard(null);
        }

        function applyEditFromButton(button) {
            if (!button) return;
            var cityName = button.getAttribute('data-city') || '';
            var cityValue = resolveCityOptionValue(cityName);
            var wardName = button.getAttribute('data-ward') || '';

            if (addressIdInput) addressIdInput.value = button.getAttribute('data-address-id') || '';
            if (recipientNameInput) recipientNameInput.value = button.getAttribute('data-recipient-name') || '';
            if (recipientPhoneInput) recipientPhoneInput.value = button.getAttribute('data-recipient-phone') || '';
            if (addressLineInput) addressLineInput.value = button.getAttribute('data-address-line') || '';
            if (isDefaultInput) isDefaultInput.checked = (button.getAttribute('data-is-default') || '0') === '1';

            setCityValue(cityValue);
            if (cityValue) {
                loadWardsByCity(cityValue, wardName);
            } else {
                resetWards();
            }
            toggleEditState(true);
            setEditingIndicator(true, button.getAttribute('data-recipient-name') || '');
            markEditingCard(button);

            if (addressForm && typeof addressForm.scrollIntoView === 'function') {
                addressForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        if (citySelect) {
            var onCityChange = function () {
                var cityId = (citySelect.value || '').trim();
                loadWardsByCity(cityId);
            };

            citySelect.addEventListener('change', onCityChange);
            if (hasSelect2) {
                window.jQuery(citySelect).on('change.select-city', onCityChange);
                window.jQuery(citySelect).trigger('change');
            } else {
                citySelect.dispatchEvent(new Event('change'));
            }
        }

        if (cancelEditBtn) {
            cancelEditBtn.addEventListener('click', function () {
                resetAddressForm();
            });
        }

        if (editButtons && editButtons.length) {
            Array.prototype.forEach.call(editButtons, function (button) {
                button.addEventListener('click', function () {
                    applyEditFromButton(button);
                });
            });
        }
        }
    } catch (e) {}

    try {
        var securityTabsWrap = document.querySelector('.js-security-tabs');
        if (securityTabsWrap) {
        var securityTabs = securityTabsWrap.querySelectorAll('.account-security-tab');
        var securityPanes = securityTabsWrap.querySelectorAll('.account-security-pane');
        var activateSecurityTab = function (tabButton) {
            if (!tabButton) return;
            var targetSelector = tabButton.getAttribute('data-target') || '';
            var targetPane = targetSelector ? securityTabsWrap.querySelector(targetSelector) : null;
            if (!targetPane) return;

            Array.prototype.forEach.call(securityTabs, function (tab) {
                var isActive = tab === tabButton;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            Array.prototype.forEach.call(securityPanes, function (pane) {
                pane.classList.toggle('is-active', pane === targetPane);
            });
        };

        Array.prototype.forEach.call(securityTabs, function (tabButton) {
            tabButton.addEventListener('click', function () {
                activateSecurityTab(tabButton);
            });
        });
        }
    } catch (e) {}

    try {
        var googleUnlinkForm = document.querySelector('.js-google-unlink-form');
        if (googleUnlinkForm) {
            var toggleBtn = googleUnlinkForm.querySelector('.js-google-unlink-toggle');
            var submitBtn = googleUnlinkForm.querySelector('.js-google-unlink-submit');
            var hiddenFields = googleUnlinkForm.querySelectorAll('.js-google-unlink-fields');
            var passwordInput = googleUnlinkForm.querySelector('input[name="current_password"]');

            if (toggleBtn && submitBtn && hiddenFields.length) {
                toggleBtn.addEventListener('click', function () {
                    Array.prototype.forEach.call(hiddenFields, function (node) {
                        node.style.display = '';
                    });
                    submitBtn.style.display = '';
                    toggleBtn.style.display = 'none';
                    if (passwordInput) {
                        passwordInput.focus();
                    }
                });
            }
        }
    } catch (e) {}

    try {
        var currentPasswordInput = document.getElementById('current-password-account');
        var newPasswordInput = document.getElementById('new-password-account');
        var newPasswordConfirmInput = document.getElementById('new-password-confirm-account');
        var passwordRulesWrap = document.querySelector('.js-password-rules');
        if (newPasswordInput && newPasswordConfirmInput && passwordRulesWrap) {
            var getRuleItem = function (name) {
                return passwordRulesWrap.querySelector('[data-rule="' + name + '"]');
            };
            var setRuleState = function (name, passed) {
                var item = getRuleItem(name);
                if (!item) return;
                item.classList.toggle('is-pass', !!passed);
            };
            var evaluateRules = function () {
                var current = currentPasswordInput ? (currentPasswordInput.value || '') : '';
                var next = newPasswordInput.value || '';
                var confirm = newPasswordConfirmInput.value || '';
                setRuleState('length', next.length >= 8);
                setRuleState('upper', /[A-Z]/.test(next));
                setRuleState('lower', /[a-z]/.test(next));
                setRuleState('digit', /[0-9]/.test(next));
                setRuleState('special', /[^A-Za-z0-9]/.test(next));
                setRuleState('not_same_current', next !== '' && next !== current);
                setRuleState('confirm_match', confirm !== '' && next === confirm);
            };

            newPasswordInput.addEventListener('input', evaluateRules);
            newPasswordConfirmInput.addEventListener('input', evaluateRules);
            if (currentPasswordInput) {
                currentPasswordInput.addEventListener('input', evaluateRules);
            }
            evaluateRules();
        }
    } catch (e) {}
})();
