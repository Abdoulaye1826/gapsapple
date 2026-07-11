@once
<script>
  /**
   * Partage une facture/un bon d'échange via WhatsApp.
   * Tente d'abord un partage natif du fichier PDF (Web Share API, niveau fichiers)
   * pour joindre réellement le document. Si le navigateur ne le permet pas
   * (la plupart des navigateurs de bureau, ou un contexte non sécurisé —
   * l'API Web Share exige HTTPS), on télécharge automatiquement le PDF en
   * plus d'ouvrir wa.me, pour que le fichier soit prêt à glisser-déposer
   * manuellement dans la conversation WhatsApp.
   */
  function downloadPdf(pdfUrl, fileName) {
    const link = document.createElement('a');
    link.href = pdfUrl;
    link.download = fileName || 'document.pdf';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
  async function shareDocumentViaWhatsApp(button) {
    const payloadUrl = button.dataset.payloadUrl;
    const icon = button.querySelector('i');
    const originalIconClass = icon ? icon.className : null;

    if (icon) {
      icon.className = 'bi bi-hourglass-split';
    }
    button.disabled = true;

    try {
      const response = await fetch(payloadUrl, { headers: { Accept: 'application/json' } });
      const data = await response.json();

      if (!response.ok) {
        alert(data.error || "Impossible de préparer l'envoi WhatsApp.");
        return;
      }

      if (navigator.canShare) {
        try {
          const pdfResponse = await fetch(data.pdfUrl);
          const blob = await pdfResponse.blob();
          const file = new File([blob], data.fileName || 'document.pdf', { type: 'application/pdf' });

          if (navigator.canShare({ files: [file] })) {
            await navigator.share({ files: [file], text: data.message });
            return;
          }
        } catch (shareError) {
          // Partage natif indisponible ou annulé : on retombe sur le lien wa.me.
        }
      }

      downloadPdf(data.pdfUrl, data.fileName);
      window.open(data.waUrl, '_blank');
    } catch (error) {
      alert("Erreur lors de la préparation de l'envoi WhatsApp.");
    } finally {
      button.disabled = false;
      if (icon && originalIconClass) {
        icon.className = originalIconClass;
      }
    }
  }

  document.addEventListener('click', function (event) {
    const button = event.target.closest('.js-whatsapp-share');
    if (button) {
      shareDocumentViaWhatsApp(button);
    }
  });
</script>
@endonce
