(function (window, document) {
    'use strict';

    if (!window.appIdleConfig) {
        return;
    }

    var config           = window.appIdleConfig;
    var timeoutMinutes   = config.timeoutMinutes || 3;
    var idleLimitMs      = timeoutMinutes * 60 * 1000;
    var warningOffsetMs  = 10 * 1000; // show warning 10 seconds before logout
    var warningAtMs      = idleLimitMs - warningOffsetMs;

    var lastActivity     = Date.now();
    var warningTimerId   = null;
    var logoutTimerId    = null;
    var pingTimerId      = null;
    var loggingOut       = false;

    function isTabVisible() {
        return document.visibilityState === 'visible';
    }

    function resetTimers() {
        lastActivity = Date.now();

        if (warningTimerId) {
            clearTimeout(warningTimerId);
            warningTimerId = null;
        }
        if (logoutTimerId) {
            clearTimeout(logoutTimerId);
            logoutTimerId = null;
        }

        scheduleTimers();
    }

    function scheduleTimers() {
        var now      = Date.now();
        var elapsed  = now - lastActivity;
        var toWarn   = Math.max(warningAtMs - elapsed, 0);
        var toLogout = Math.max(idleLimitMs - elapsed, 0);

        warningTimerId = setTimeout(showWarning, toWarn);
        logoutTimerId  = setTimeout(performLogout, toLogout);
    }

    function showWarning() {
        // If tab isn't visible, don't bother — re-check when it becomes visible again
        if (!isTabVisible()) {
            resetTimers();
            return;
        }

        var warningText = "Approaching Idle time Max, you’re about to be logged out";

        // If SweetAlert2 is available, use it; otherwise fallback to confirm()
        if (window.Swal && typeof window.Swal.fire === 'function') {
            Swal.fire({
                title: 'Session Idle Warning',
                text: warningText,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Stay signed in',
                cancelButtonText: 'Log out now',
                allowOutsideClick: false,
                allowEscapeKey: false,
            }).then(function (result) {
                if (result.isConfirmed) {
                    resetTimers();
                } else {
                    performLogout();
                }
            });
        } else {
            var stay = window.confirm(warningText + '\n\nClick "OK" to stay signed in.');
            if (stay) {
                resetTimers();
            } else {
                performLogout();
            }
        }
    }

    function performLogout() {
        if (loggingOut) {
            return;
        }
        loggingOut = true;

        function redirectToLogin(loginUrl) {
            try {
                var url = new URL(loginUrl, window.location.origin);
                url.searchParams.set('reason', 'idle');
                window.location.href = url.toString();
            } catch (e) {
                // Fallback if URL constructor not available
                if (loginUrl.indexOf('?') === -1) {
                    window.location.href = loginUrl + '?reason=idle';
                } else {
                    window.location.href = loginUrl + '&reason=idle';
                }
            }
        }

        fetch(config.logoutUrl, {
            method: 'GET',
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json().catch(function () {
                    return {};
                });
            })
            .then(function (data) {
                redirectToLogin(data.redirect || '/login');
            })
            .catch(function () {
                redirectToLogin('/login');
            });
    }

    // Activity handler – only when tab is visible
    function handleActivity() {
        if (!isTabVisible()) {
            return;
        }
        resetTimers();
    }

    // Hook typical user activity events (UI-only; strict, no AJAX)
    var activityEvents = ['mousemove', 'keydown', 'click', 'scroll', 'focus'];
    activityEvents.forEach(function (evt) {
        window.addEventListener(evt, handleActivity, { passive: true });
    });

    // When tab visibility changes, enforce strict idle
    document.addEventListener('visibilitychange', function () {
        if (!isTabVisible()) {
            return;
        }

        var now = Date.now();
        if (now - lastActivity >= idleLimitMs) {
            // User has been idle past the limit while tab was hidden/blurred
            performLogout();
        } else {
            // Tab came back before limit; resync timers
            resetTimers();
        }
    });

    // Periodic ping to backend to confirm session still valid
    function pingSession() {
        fetch(config.pingUrl, {
            method: 'GET',
            credentials: 'same-origin'
        })
            .then(function (resp) {
                if (resp.status === 401) {
                    performLogout();
                }
                // 200 is OK, do nothing
            })
            .catch(function () {
                // Network error; ignore – strict UI idle will still handle
            });
    }

    pingTimerId = setInterval(pingSession, 60 * 1000); // every 60 seconds

    // Initial start
    resetTimers();
})(window, document);
