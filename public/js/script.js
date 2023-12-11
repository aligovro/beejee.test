document.addEventListener('DOMContentLoaded', function () {
    let updateTaskBtn = document.getElementById('updateTaskBtn');
    if (updateTaskBtn) {
        updateTaskBtn.addEventListener('click', function () {
            let taskUsername = document.getElementById('username').value;
            let taskEmail = document.getElementById('email').value;
            let taskText = document.getElementById('text').value;
            let taskStatus = document.getElementById('status')?.checked ? 1 : 0;
            let taskId = document.getElementById('taskId')?.value;

            validateRequiredField(taskUsername, 'username');
            validateRequiredField(taskText, 'text');

            if (!isValidEmail(taskEmail)) {
                validateAndDisplayError('.invalid-feedback.email');
                return;
            }

            if (hasVisibleErrors()) {
                return;
            }

            sendAjaxRequest('/tasks/update', 'POST', {
                taskUsername: taskUsername,
                taskEmail: taskEmail,
                taskText: taskText,
                taskStatus: taskStatus,
                taskId: taskId
            }, function (response) {
                if (response.success && taskId) {
                    showSuccessMessage('Task updated successfully!');
                } else if (response.success) {
                    showSuccessMessage('Task created successfully!');
                }
            });
        });
    }

    let usernameInput = document.getElementById('username');
    let textInput = document.getElementById('text');
    let emailInput = document.getElementById('email');

    if (usernameInput && textInput && emailInput) {
        usernameInput.addEventListener('input', function () {
            validateAndHideError('.invalid-feedback.username');
        });

        textInput.addEventListener('input', function () {
            validateAndHideError('.invalid-feedback.text');
        });

        emailInput.addEventListener('input', function () {
            validateAndHideError('.invalid-feedback.email');
        });
    }

    updateSortSelectValue('sortSelect', 'sort');
    updateSortSelectValue('sortTypeSelect', 'sortType');

    let loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            event.preventDefault();

            let username = document.getElementById('loginUsername').value;
            let password = document.getElementById('loginPassword').value;

            sendAjaxRequest('/auth/login', 'POST', {
                username: username,
                password: password
            }, function (response) {
                if (response.success) {
                    window.location.href = '/';
                } else {
                    showErrorMessage('Authentication failed. Please check your credentials.');
                }
            }, function (status, statusText) {
                showErrorMessage(`Error: ${status} - ${statusText}`);
            });
        });
    }
});

const updateSortSelectValue = (selectId, paramName) => {
    let currentUrl = new URL(window.location.href);
    let currentValue = currentUrl.searchParams.get(paramName);
    let selectElement = document.getElementById(selectId);
    if (currentValue) {
        selectElement.value = currentValue;
    }
};

function sendAjaxRequest(url, method, data, successCallback, errorCallback) {
    let xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.onreadystatechange = function () {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                try {
                    let response = JSON.parse(xhr.responseText);
                    if (successCallback && typeof successCallback === 'function') {
                        successCallback(response);
                    }
                } catch (error) {
                    console.error('Ошибка парсинга JSON:', error);
                    console.log('Ответ сервера:', xhr.responseText);
                }
            } else {
                let response = JSON.parse(xhr.responseText);
                if (errorCallback) {
                    errorCallback(xhr.status, response.error || xhr.statusText);
                }
            }
        }
    };
    xhr.send(JSON.stringify(data));
}

function showAlert(message, type) {
    let messageElement = document.createElement('div');
    messageElement.className = `alert alert-${type} mt-3`;
    messageElement.textContent = message;
    let showAlert = document.getElementById('showAlert');
    if (showAlert) {
        showAlert.appendChild(messageElement);
    }
    setTimeout(function () {
        messageElement.remove();
    }, 3000);
}

function showSuccessMessage(message) {
    showAlert(message, 'success');
}

function showErrorMessage(message) {
    showAlert(message, 'danger');
}
function isValidEmail(email) {
    let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}
function sortTasks(sortBy) {
    let currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('sort', sortBy);
    window.location.href = currentUrl.toString();
}
function sortSortByType(sortByType) {
    let currentUrl = new URL(window.location.href);
    currentUrl.searchParams.set('sortType', sortByType);
    window.location.href = currentUrl.toString();
}
function validateRequiredField(value, fieldName) {
    let errorBlockSelector = `.invalid-feedback.${fieldName}`;
    if (!value) {
        validateAndDisplayError(errorBlockSelector);
    } else {
        validateAndHideError(errorBlockSelector);
    }
}
function validateAndDisplayError(selector) {
    let invalidFeedbackElement = document.querySelector(selector);
    if (invalidFeedbackElement !== null) {
        invalidFeedbackElement.style.display = 'block';
    }
    return false;
}
function validateAndHideError(selector) {
    let invalidFeedbackElement = document.querySelector(selector);
    if (invalidFeedbackElement !== null) {
        invalidFeedbackElement.style.display = 'none';
    }
    return true;
}

function hasVisibleErrors() {
    let errorBlocks = document.querySelectorAll('.invalid-feedback');
    return Array.from(errorBlocks).some(block => window.getComputedStyle(block).display !== 'none');
}
