// resources/js/Composables/useTooltip.js
import { ref } from 'vue';

export function useTooltip() {
  const tooltipData = ref(null);
  const tooltipTarget = ref(null);
  const show = ref(false);
  const longPressTimer = ref(null);
  const isTouch = ref(false);

  function showTooltip(data, target, placement = 'top') {
    tooltipData.value = data;
    tooltipTarget.value = target;
    show.value = true;
    
    // Forzar posicionamiento en el siguiente tick
    requestAnimationFrame(() => {
      if (window.tooltipComponent) {
        window.tooltipComponent.positionTooltip(target);
      }
    });
  }

  function hideTooltip() {
    show.value = false;
    tooltipData.value = null;
    tooltipTarget.value = null;
    clearTimeout(longPressTimer.value);
  }

  // Hover events (desktop)
  function onMouseEnter(e, data) {
    if (isTouch.value) return;
    showTooltip(data, e.currentTarget, 'top');
  }

  function onMouseLeave() {
    if (isTouch.value) return;
    hideTooltip();
  }

  // Long press events (mobile)
    function onTouchStart(e, data) {
    isTouch.value = true;
    const target = e.currentTarget;
    
    // MUESTRA INMEDIATAMENTE sin temporizador
    showTooltip(data, target, 'top');
    
    // Opcional: añadir feedback visual
    target.classList.add('touch-active');
    }

    function onTouchEnd() {
    // NO cerrar al soltar, solo limpiar clases
    document.querySelectorAll('.touch-active').forEach(el => {
        el.classList.remove('touch-active');
    });
    clearTimeout(longPressTimer.value);
    }

  function onTouchMove() {
    // Cancelar si se mueve el dedo
    clearTimeout(longPressTimer.value);
    hideTooltip();
  }

  return {
    tooltipData,
    show,
    showTooltip,
    hideTooltip,
    onMouseEnter,
    onMouseLeave,
    onTouchStart,
    onTouchEnd,
    onTouchMove
  };
}