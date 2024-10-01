function restrictToNumbers(event) {
  // Allow navigation keys (backspace, arrow keys, etc.)
  if (
    ["Backspace", "ArrowLeft", "ArrowRight", "Tab", "Delete"].indexOf(
      event.key
    ) !== -1
  ) {
    return;
  }
  // Allow only numbers
  if (!/[\d]/.test(event.key)) {
    event.preventDefault();
  }
}
