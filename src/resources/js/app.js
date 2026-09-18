import '../css/app.css';

import SignaturePad from 'signature_pad';

document.addEventListener('DOMContentLoaded', () => {
    const focusTarget = document.querySelector('[data-focus-on-load="true"]');
    if (focusTarget) {
        requestAnimationFrame(() => focusTarget.focus());
    }

    const headquartersSelector = document.querySelector('[data-headquarter-selector]');

    if (headquartersSelector) {
        const checkboxes = [...headquartersSelector.querySelectorAll('input[name="headquarter_ids[]"]')];
        const status = headquartersSelector.querySelector('#headquarter-selection-status');
        const selectAllButton = headquartersSelector.querySelector('[data-select-all-headquarters]');
        const clearButton = headquartersSelector.querySelector('[data-clear-headquarters]');

        function updateHeadquarterStatus() {
            const selectedCount = checkboxes.filter((checkbox) => checkbox.checked).length;

            if (!status) return;

            status.textContent = selectedCount
                ? `${selectedCount} sede${selectedCount === 1 ? '' : 's'} seleccionada${selectedCount === 1 ? '' : 's'}.`
                : 'No se seleccionó ninguna sede: se incluirán todas.';
        }

        selectAllButton?.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = true;
            });
            updateHeadquarterStatus();
        });

        clearButton?.addEventListener('click', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = false;
            });
            updateHeadquarterStatus();
        });

        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateHeadquarterStatus));
        updateHeadquarterStatus();
    }

    const canvas = document.getElementById('signatureCanvas');

    if (!canvas) return;

    const signatureInput = document.getElementById('signatureInput');
    const clearBtn = document.getElementById('clearSignature');
    const useTypedSignatureBtn = document.getElementById('useTypedSignature');
    const form = document.getElementById('attendanceForm');
    const placeholder = document.getElementById('signaturePlaceholder');
    const statusEl = document.getElementById('signatureStatus');
    const firstNamesInput = document.getElementById('first_names');
    const lastNamesInput = document.getElementById('last_names');
    const signatureFrame = canvas.closest('.signature-frame');
    let typedSignatureName = null;

    function getParticipantName() {
        return [firstNamesInput?.value.trim(), lastNamesInput?.value.trim()]
            .filter(Boolean)
            .join(' ');
    }

    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(0,0,0,0)',
        penColor: '#0a1628',
        minWidth: 1,
        maxWidth: 2.5,
    });

    function setStatus(message = '', isError = false) {
        if (!statusEl) return;

        statusEl.textContent = message;
        statusEl.classList.toggle('text-red-600', isError);
        statusEl.classList.toggle('text-warm-600', !isError);
    }

    function hasSignature() {
        return Boolean(typedSignatureName) || !signaturePad.isEmpty();
    }

    function setSignatureInvalid(isInvalid) {
        signatureFrame?.classList.toggle('is-invalid', isInvalid);

        if (isInvalid) {
            signatureFrame?.setAttribute('aria-invalid', 'true');
            return;
        }

        signatureFrame?.removeAttribute('aria-invalid');
    }

    function updateUI(message = null, isError = false) {
        const isEmpty = !hasSignature();

        if (placeholder) {
            placeholder.style.opacity = isEmpty ? '1' : '0';
        }

        if (clearBtn) {
            clearBtn.disabled = isEmpty;
            clearBtn.setAttribute('aria-disabled', String(isEmpty));
        }

        if (!isEmpty) {
            setSignatureInvalid(false);
        }

        if (message !== null) {
            setStatus(message, isError);
            return;
        }

        if (isEmpty) {
            setStatus('');
            return;
        }

        setStatus(typedSignatureName ? 'Firma generada con el nombre indicado.' : 'Firma dibujada correctamente.');
    }

    function drawTypedSignature(name, announce = true) {
        const rect = canvas.getBoundingClientRect();
        const ctx = canvas.getContext('2d');

        ctx.clearRect(0, 0, rect.width, rect.height);
        ctx.save();
        ctx.fillStyle = '#0a1628';
        ctx.strokeStyle = '#c9952f';
        ctx.lineWidth = 1.5;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';

        let fontSize = 34;
        do {
            ctx.font = `italic ${fontSize}px Georgia, serif`;
            fontSize -= 2;
        } while (ctx.measureText(name).width > rect.width - 48 && fontSize > 20);

        ctx.fillText(name, rect.width / 2, rect.height / 2 - 4);
        ctx.beginPath();
        ctx.moveTo(24, rect.height / 2 + 30);
        ctx.lineTo(rect.width - 24, rect.height / 2 + 30);
        ctx.stroke();
        ctx.restore();

        signatureInput.value = canvas.toDataURL('image/png');

        if (announce) {
            updateUI('Firma generada con el nombre indicado.');
        }
    }

    function resizeCanvas() {
        const rect = canvas.getBoundingClientRect();
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = Math.max(rect.width * ratio, 1);
        canvas.height = Math.max(rect.height * ratio, 1);

        const ctx = canvas.getContext('2d');
        ctx.scale(ratio, ratio);

        if (typedSignatureName) {
            drawTypedSignature(typedSignatureName, false);
            updateUI();
            return;
        }

        signaturePad.clear();
        updateUI();
    }

    useTypedSignatureBtn?.addEventListener('click', () => {
        const firstNames = firstNamesInput?.value.trim();
        const lastNames = lastNamesInput?.value.trim();
        const name = getParticipantName();

        if (!firstNames || !lastNames) {
            setStatus('Escriba sus nombres y apellidos antes de generar la firma.', true);
            (firstNames ? lastNamesInput : firstNamesInput)?.focus();
            return;
        }

        typedSignatureName = name;
        signaturePad.clear();
        drawTypedSignature(name);
    });

    signaturePad.addEventListener('beginStroke', () => {
        typedSignatureName = null;
        signatureInput.value = '';
    });

    signaturePad.addEventListener('endStroke', () => {
        updateUI('Firma dibujada correctamente.');
    });

    clearBtn?.addEventListener('click', () => {
        typedSignatureName = null;
        signaturePad.clear();
        signatureInput.value = '';
        updateUI('Firma borrada.');
    });

    form?.addEventListener('submit', (event) => {
        if (!hasSignature()) {
            event.preventDefault();
            setSignatureInvalid(true);
            updateUI('Debe registrar su firma antes de enviar.', true);
            useTypedSignatureBtn?.focus();
            return;
        }

        if (!typedSignatureName) {
            signatureInput.value = signaturePad.toDataURL();
        }

        const btn = document.getElementById('submitBtn');
        if (!btn) return;

        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
        btn.innerHTML = '<span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4" aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg> Registrando...</span>';
    });

    if ('ResizeObserver' in window) {
        const ro = new ResizeObserver(() => resizeCanvas());
        ro.observe(canvas);
        resizeCanvas();
    } else {
        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();
    }

    updateUI();
});
