document.addEventListener('DOMContentLoaded', function () {
    var yearSelect = document.getElementById('cdb_empleado_year');
    var equipoSelect = document.getElementById('cdb_empleado_equipo');
    var equiposData = window.cdbEmpleadoEquiposData || [];

    function updateEquipos() {
        var selectedYear = yearSelect.value;
        equipoSelect.innerHTML = '<option value="">' + cdbEmpleadoTexts.select_team + '</option>';
        equiposData.forEach(function(equipo) {
            if (equipo._cdb_equipo_year == selectedYear) {
                var option = document.createElement('option');
                option.value = equipo.ID;
                option.textContent = equipo.post_title;
                equipoSelect.appendChild(option);
            }
        });
    }

    if (yearSelect && equipoSelect) {
        yearSelect.addEventListener('change', updateEquipos);
        updateEquipos();
    }
});
