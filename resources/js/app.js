import { animate, scroll } from 'motion';

document.addEventListener('DOMContentLoaded', () => {
  const revealElements = document.querySelectorAll('.reveal');
  revealElements.forEach(el => {
    scroll(
      animate(el, { opacity: [0, 1], y: [30, 0] }, { duration: 0.6, easing: 'ease-out' }),
      {
        target: el,
        offset: ['start end', 'end start']
      }
    );
  });
});
