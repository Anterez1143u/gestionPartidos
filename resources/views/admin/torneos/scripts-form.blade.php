<script>
    (function(){
        const incBtn = document.getElementById('incBtn');
        const decBtn = document.getElementById('decBtn');
        const numInput = document.getElementById('num_participantes');
        const radios = document.querySelectorAll('.fase-radio');
        const unicaGroup = document.getElementById('unicaGroup');
        const multiGroup = document.getElementById('multiGroup');

        incBtn.addEventListener('click', () => {
            numInput.value = Math.max(2, parseInt(numInput.value || 0) + 1);
        });
        decBtn.addEventListener('click', () => {
            numInput.value = Math.max(2, parseInt(numInput.value || 0) - 1);
        });

        function updateFormato() {
            const val = document.querySelector('input[name="fase_tipo"]:checked').value;
            if (val === 'unica') {
                multiGroup.classList.add('hidden');
                unicaGroup.classList.remove('hidden');
                multiGroup.querySelectorAll('select').forEach(s => s.disabled = true);
                unicaGroup.querySelector('select').disabled = false;
            } else {
                unicaGroup.classList.add('hidden');
                multiGroup.classList.remove('hidden');
                unicaGroup.querySelector('select').disabled = true;
                multiGroup.querySelectorAll('select').forEach(s => s.disabled = false);
            }
        }

        radios.forEach(r => r.addEventListener('change', updateFormato));
        updateFormato();
    })();
</script>