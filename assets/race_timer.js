document.querySelectorAll('.race-timer').forEach(function (element) {
    if (!element.dataset.startTime) {
        return;
    }

    const startDate = new Date(element.dataset.startTime * 1000).getTime();

    const updateInterval = setInterval(function () {
        const now = new Date().getTime();

        const negative = startDate > now;
        const diff = Math.abs(now - startDate);

        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        const milliseconds = Math.floor(diff % 1000);

        let output = '';
        if (hours > 0) output += hours.toString() + ':';
        if (!negative || minutes > 0 || hours > 0) output += minutes.toString().padStart(2, '0') + ':';
        output += seconds.toString().padStart(2, '0') + '.';
        output += milliseconds.toString().padStart(3, '0');

        element.innerHTML = negative ? '<span class="text-danger">' + output + '</span>' : output;
    }, 10);

    document.querySelectorAll('.race-timer-stopper').forEach(function (stopper) {
        stopper.addEventListener('submit', function (event) {
            clearInterval(updateInterval);
        });
    });
});
