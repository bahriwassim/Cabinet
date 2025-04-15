/**
 * Prescription functionality for MedOffice Manager
 *
 * @link       https://stikyconsulting.com
 * @since      1.0.0
 */

(function($) {
    'use strict';

    /**
     * Initialize the prescription functionality
     */
    function initPrescription() {
        // Only initialize if the print-prescription-btn exists
        if (!$('#print-prescription-btn').length) {
            return;
        }

        // Print prescription button click handler
        $(document).on('click', '#print-prescription-btn', function(e) {
            e.preventDefault();
            
            const consultationId = $(this).data('consultation-id') || currentConsultationId;
            if (!consultationId) {
                alert('ID de consultation non défini.');
                return;
            }
            
            generatePrescriptionPDF(consultationId);
        });
    }
    
    /**
     * Generate and download PDF prescription
     */
    function generatePrescriptionPDF(consultationId) {
        $.ajax({
            url: medoffice_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'medoffice_get_prescription',
                nonce: medoffice_ajax.nonce,
                consultation_id: consultationId
            },
            success: function(response) {
                if (response.success) {
                    const prescription = response.data;
                    createPrescriptionPDF(prescription);
                } else {
                    alert('Erreur lors du chargement des données de l\'ordonnance.');
                }
            },
            error: function() {
                alert('Erreur de communication avec le serveur.');
            }
        });
    }
    
    /**
     * Create PDF prescription using jsPDF
     */
    function createPrescriptionPDF(prescription) {
        // A5 size in mm: 148 x 210
        const pageWidth = 148;
        const pageHeight = 210;
        const margin = 10;
        
        // Create a new jsPDF instance
        const doc = new jspdf.jsPDF({
            orientation: 'portrait',
            unit: 'mm',
            format: 'a5'
        });
        
        // Set font
        doc.setFont('helvetica');
        
        // Header
        doc.setFontSize(16);
        doc.setTextColor(0, 0, 100);
        doc.text(prescription.nom_cabinet || 'Cabinet Médical', margin, margin + 5);
        
        doc.setFontSize(11);
        doc.setTextColor(0, 0, 0);
        doc.text('Dr. ' + (prescription.nom_medecin || ''), margin, margin + 12);
        doc.text(prescription.specialite || '', margin, margin + 18);
        doc.text(prescription.adresse_cabinet || '', margin, margin + 24);
        doc.text('Tél: ' + (prescription.telephone_cabinet || ''), margin, margin + 30);
        
        // Title
        doc.setFontSize(14);
        doc.setFont('helvetica', 'bold');
        doc.text('ORDONNANCE', pageWidth / 2, margin + 40, { align: 'center' });
        doc.line(margin, margin + 43, pageWidth - margin, margin + 43);
        
        // Patient info
        doc.setFontSize(11);
        doc.setFont('helvetica', 'normal');
        doc.text('Patient: ' + (prescription.patient_name || ''), margin, margin + 53);
        doc.text('Âge: ' + (prescription.patient_age || ''), margin, margin + 60);
        doc.text('Date: ' + new Date(prescription.date_consultation).toLocaleDateString(), pageWidth - margin, margin + 53, { align: 'right' });
        
        // Content
        const contentX = margin;
        const contentY = margin + 70;
        const contentWidth = pageWidth - (2 * margin);
        const contentHeight = pageHeight - contentY - 25; // Leave space for footer
        
        // Split the content into lines
        const prescriptionText = prescription.contenu || 'Aucun contenu dans l\'ordonnance';
        const textLines = doc.splitTextToSize(prescriptionText, contentWidth);
        
        doc.setFontSize(10);
        
        // Add text with proper line breaks
        let y = contentY;
        for (let i = 0; i < textLines.length; i++) {
            // Check if we need to add a new page
            if (y > pageHeight - margin - 25) {
                doc.addPage();
                y = margin + 10;
            }
            
            doc.text(textLines[i], contentX, y);
            y += 5;
        }
        
        // Footer with signature
        const footerY = pageHeight - margin - 15;
        doc.setFontSize(10);
        doc.text('Signature', pageWidth - margin, footerY, { align: 'right' });
        doc.line(pageWidth - 50, footerY + 10, pageWidth - margin, footerY + 10);
        
        // Save the PDF with a proper filename
        const filename = 'ordonnance_' + prescription.patient_name.replace(/\s+/g, '_').toLowerCase() + '_' + 
                         new Date(prescription.date_consultation).toISOString().split('T')[0] + '.pdf';
        
        doc.save(filename);
    }

    /**
     * Document ready handler
     */
    $(document).ready(function() {
        initPrescription();
    });

})(jQuery);
