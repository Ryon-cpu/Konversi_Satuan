document.addEventListener('DOMContentLoaded', function () {
    const categorySelect = document.getElementById('category');
    const fromUnitSelect = document.getElementById('from_unit');
    const toUnitSelect = document.getElementById('to_unit');

    if (!categorySelect || !fromUnitSelect || !toUnitSelect || !window.converterState) {
        return;
    }

    categorySelect.value = window.converterState.category;
    fromUnitSelect.value = window.converterState.fromUnit;
    toUnitSelect.value = window.converterState.toUnit;

    categorySelect.addEventListener('change', function () {
        const options = Array.from(fromUnitSelect.options).map((option) => option.value);
        if (!options.includes(window.converterState.fromUnit)) {
            window.converterState.fromUnit = options[0];
        }
        if (!options.includes(window.converterState.toUnit)) {
            window.converterState.toUnit = options[1] || options[0];
        }

        fromUnitSelect.value = window.converterState.fromUnit;
        toUnitSelect.value = window.converterState.toUnit;
    });
});