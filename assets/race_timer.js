const referenceTime = new Date().getTime();

document.querySelectorAll('.race-timer').forEach(function (element) {
    if (!element.dataset.deltaTime) {
        return;
    }

    const startTime = referenceTime - element.dataset.deltaTime;

    const updateInterval = setInterval(function () {
        const now = new Date().getTime();

        const negative = startTime > now;
        const diff = Math.abs(now - startTime);

        const hours = Math.floor(diff / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        const milliseconds = Math.floor(diff % 1000);

        let output = '';
        if (hours > 0) output += hours.toString() + ':';
        if (!negative || minutes > 0 || hours > 0) output += minutes.toString().padStart(2, '0') + ':';
        output += seconds.toString().padStart(2, '0') + '.';
        output += '<small>' + Math.floor(milliseconds / 10).toString().padStart(2, '0') + '</small>';

        element.innerHTML = negative ? '<span class="text-danger">' + output + '</span>' : output;
    }, 10);

    document.querySelectorAll('.race-timer-stopper').forEach(function (stopper) {
        stopper.addEventListener('submit', function (event) {
            clearInterval(updateInterval);
        });
    });
});
