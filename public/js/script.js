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

    let currentUrl = new URL(window.location.href);
    let currentSort = currentUrl.searchParams.get('sort');
    let sortSelect = document.getElementById('sortSelect');
    if (currentSort) {
        sortSelect.value = currentSort;
    }
});

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
                if (errorCallback && typeof errorCallback === 'function') {
                    errorCallback(xhr.status, xhr.statusText);
                }
            }
        }
    };
    xhr.send(JSON.stringify(data));
}

function showSuccessMessage(message) {
    let successMessageElement = document.createElement('div');
    successMessageElement.className = 'alert alert-success mt-3';
    successMessageElement.textContent = message;
    let formElement = document.getElementById('addTaskForm');
    if (formElement) {
        formElement.parentNode.insertBefore(successMessageElement, formElement);
    }
    setTimeout(function () {
        successMessageElement.style.display = 'none';
    }, 3000);
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
