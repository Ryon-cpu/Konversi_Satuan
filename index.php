<?php
$unitOptions = [
    'length' => [
        'meter' => ['label' => 'Meter', 'to_base' => 1],
        'kilometer' => ['label' => 'Kilometer', 'to_base' => 1000],
        'centimeter' => ['label' => 'Sentimeter', 'to_base' => 0.01],
        'millimeter' => ['label' => 'Milimeter', 'to_base' => 0.001],
        'mile' => ['label' => 'Mil', 'to_base' => 1609.344],
        'yard' => ['label' => 'Yard', 'to_base' => 0.9144],
        'foot' => ['label' => 'Kaki', 'to_base' => 0.3048],
        'inch' => ['label' => 'Inci', 'to_base' => 0.0254],
    ],
    'weight' => [
        'kilogram' => ['label' => 'Kilogram', 'to_base' => 1],
        'gram' => ['label' => 'Gram', 'to_base' => 0.001],
        'milligram' => ['label' => 'Miligram', 'to_base' => 0.000001],
        'pound' => ['label' => 'Pon', 'to_base' => 0.45359237],
        'ounce' => ['label' => 'Ons', 'to_base' => 0.028349523125],
    ],
    'temperature' => [
        'celsius' => ['label' => 'Celsius', 'to_base' => 1],
        'fahrenheit' => ['label' => 'Fahrenheit', 'to_base' => 1],
        'kelvin' => ['label' => 'Kelvin', 'to_base' => 1],
    ],
];

function formatNumber(float $value): string
{
    if (abs($value) >= 1000000) {
        return number_format($value, 2, ',', '.');
    }

    if (floor($value) == $value) {
        return number_format($value, 0, ',', '.');
    }

    return rtrim(rtrim(number_format($value, 12, ',', '.'), '0'), ',');
}

function convertValue(float $value, string $category, string $fromUnit, string $toUnit): float
{
    if ($category === 'temperature') {
        $celsius = match ($fromUnit) {
            'celsius' => $value,
            'fahrenheit' => ($value - 32) * 5 / 9,
            'kelvin' => $value - 273.15,
            default => $value,
        };

        return match ($toUnit) {
            'celsius' => $celsius,
            'fahrenheit' => ($celsius * 9 / 5) + 32,
            'kelvin' => $celsius + 273.15,
            default => $celsius,
        };
    }

    $fromFactor = $GLOBALS['unitOptions'][$category][$fromUnit]['to_base'];
    $toFactor = $GLOBALS['unitOptions'][$category][$toUnit]['to_base'];

    if ($fromFactor <= 0 || $toFactor <= 0) {
        return 0;
    }

    return ($value * $fromFactor) / $toFactor;
}

$selectedCategory = $_POST['category'] ?? 'length';
if (!isset($unitOptions[$selectedCategory])) {
    $selectedCategory = 'length';
}

$categoryUnitKeys = array_keys($unitOptions[$selectedCategory]);
$selectedFrom = $_POST['from_unit'] ?? $categoryUnitKeys[0];
$selectedTo = $_POST['to_unit'] ?? ($categoryUnitKeys[1] ?? $categoryUnitKeys[0]);

if (!isset($unitOptions[$selectedCategory][$selectedFrom])) {
    $selectedFrom = $categoryUnitKeys[0];
}

if (!isset($unitOptions[$selectedCategory][$selectedTo]) || $selectedTo === $selectedFrom) {
    $selectedTo = $categoryUnitKeys[1] ?? $categoryUnitKeys[0];
}

$inputValue = $_POST['value'] ?? '';
$convertedValue = null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['convert'])) {
    $inputValue = trim((string) $_POST['value']);

    if ($inputValue === '' || !is_numeric($inputValue)) {
        $message = 'Masukkan angka yang valid.';
    } elseif (!isset($unitOptions[$selectedCategory][$selectedFrom]) || !isset($unitOptions[$selectedCategory][$selectedTo])) {
        $message = 'Pilih pasangan satuan yang valid.';
    } else {
        $numericValue = (float) $inputValue;
        $convertedValue = convertValue($numericValue, $selectedCategory, $selectedFrom, $selectedTo);
        $fromLabel = $unitOptions[$selectedCategory][$selectedFrom]['label'];
        $toLabel = $unitOptions[$selectedCategory][$selectedTo]['label'];
        $message = sprintf('%s %s = %s %s', formatNumber($numericValue), $fromLabel, formatNumber($convertedValue), $toLabel);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konverter Satuan</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="app-shell">
        <section class="converter-card" aria-label="Konverter satuan">
            <header class="card-header">
                <p class="eyebrow">Konversi Satuan</p>
                <h1>Konverter Satuan</h1>
            </header>

            <form method="post" class="converter-form">
                <div class="field-group">
                    <label for="category">Kategori</label>
                    <select id="category" name="category">
                        <option value="length" <?php echo $selectedCategory === 'length' ? 'selected' : ''; ?>>Panjang</option>
                        <option value="weight" <?php echo $selectedCategory === 'weight' ? 'selected' : ''; ?>>Berat</option>
                        <option value="temperature" <?php echo $selectedCategory === 'temperature' ? 'selected' : ''; ?>>Suhu</option>
                    </select>
                </div>

                <div class="field-group">
                    <label for="value">Nilai</label>
                    <input
                        id="value"
                        name="value"
                        type="number"
                        step="any"
                        inputmode="decimal"
                        value="<?php echo htmlspecialchars($inputValue, ENT_QUOTES, 'UTF-8'); ?>"
                        placeholder="Masukkan nilai"
                        required
                    >
                </div>

                <div class="unit-grid">
                    <div class="field-group">
                        <label for="from_unit">Dari</label>
                        <select id="from_unit" name="from_unit"></select>
                    </div>

                    <div class="field-group">
                        <label for="to_unit">Ke</label>
                        <select id="to_unit" name="to_unit"></select>
                    </div>
                </div>

                <button type="submit" name="convert" value="1">Konversi</button>
            </form>

            <?php if ($message !== ''): ?>
                <div class="result-box <?php echo $convertedValue !== null ? 'success' : 'error'; ?>">
                    <span class="result-label">Hasil</span>
                    <p><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></p>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <script>
        window.converterState = {
            category: '<?php echo htmlspecialchars($selectedCategory, ENT_QUOTES, 'UTF-8'); ?>',
            fromUnit: '<?php echo htmlspecialchars($selectedFrom, ENT_QUOTES, 'UTF-8'); ?>',
            toUnit: '<?php echo htmlspecialchars($selectedTo, ENT_QUOTES, 'UTF-8'); ?>'
        };

        const unitOptions = <?php echo json_encode($unitOptions, JSON_UNESCAPED_SLASHES); ?>;
        const categorySelect = document.getElementById('category');
        const fromUnitSelect = document.getElementById('from_unit');
        const toUnitSelect = document.getElementById('to_unit');

        function populateUnits(selectedCategory) {
            const units = unitOptions[selectedCategory];
            const selectedFrom = window.converterState.fromUnit;
            const selectedTo = window.converterState.toUnit;

            fromUnitSelect.innerHTML = '';
            toUnitSelect.innerHTML = '';

            Object.entries(units).forEach(([unitKey, unitData]) => {
                const fromOption = document.createElement('option');
                fromOption.value = unitKey;
                fromOption.textContent = unitData.label;
                if (unitKey === selectedFrom) {
                    fromOption.selected = true;
                }
                fromUnitSelect.appendChild(fromOption);

                const toOption = document.createElement('option');
                toOption.value = unitKey;
                toOption.textContent = unitData.label;
                if (unitKey === selectedTo) {
                    toOption.selected = true;
                }
                toUnitSelect.appendChild(toOption);
            });

            fromUnitSelect.value = selectedFrom;
            toUnitSelect.value = selectedTo;
        }

        categorySelect.addEventListener('change', function () {
            window.converterState.category = this.value;
            window.converterState.fromUnit = Object.keys(unitOptions[this.value])[0];
            window.converterState.toUnit = Object.keys(unitOptions[this.value])[1] || Object.keys(unitOptions[this.value])[0];
            populateUnits(this.value);
        });

        populateUnits(window.converterState.category);
    </script>
</body>
</html>