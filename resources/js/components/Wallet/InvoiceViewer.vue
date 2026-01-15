<template>
  <div class="invoice-wrapper">
    <!-- Action Buttons -->
    <div class="action-buttons mb-4 flex justify-center gap-3">
      <button @click="downloadPDF" class="btn-download flex items-center gap-2">
        <i class="fas fa-download"></i>
        Download PDF
      </button>
    </div>

    <!-- Invoice Content -->
    <div v-if="loading" class="flex items-center justify-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-pink-500"></div>
    </div>

    <div v-else-if="error" class="text-center py-20">
      <i class="fas fa-exclamation-triangle text-red-500 text-4xl mb-4"></i>
      <p class="text-red-600">{{ error }}</p>
    </div>

    <div v-else id="invoice-content" class="invoice-container">
      <!-- Header -->
      <div class="header">
        <h1>INVOICE</h1>
        <p>Namate24 - Your Learning Platform</p>
      </div>

      <!-- Company & Registration Details in Line -->
      <div class="top-details-row">
        <div class="company-details-section">
          <h3>Company Details</h3>
          <strong>Namate24 Training Services OPC Pvt Ltd</strong><br>
          Building 154, 3rd Cross, Golahalli Main,<br>
          Electronic City Phase 1,<br>
          Bengaluru, Karnataka, India – 560100<br>
          Email: trainhireh@gmail.com
        </div>

        <div class="bank-details">
          <h3>Company Registration</h3>
          GST No: 29AAICN7245B1ZE<br>
          CIN: U70200KA20230PC17076
        </div>
      </div>

      <!-- Invoice Info -->
      <div class="invoice-info">
        <div>
          <h3>Invoice To:</h3>
          <p v-if="invoice.user?.name"><strong>{{ invoice.user.name }}</strong></p>
          <p>{{ invoice.user?.email }}</p>
          <p v-if="invoice.user?.address && invoice.user?.city && invoice.user?.area && invoice.user?.country" style="margin-top: 5px; font-size: 11px; color: #666;">
            {{ invoice.user?.address }}<br>
            {{ invoice.user?.area }}<br>
            {{ invoice.user?.city }}<br>
            {{ invoice.user?.country }}
          </p>
        </div>
        <div style="text-align: right;">
          <h3>Invoice Details:</h3>
          <p><strong>Invoice #:</strong> {{ invoice.invoice_number }}</p>
          <p><strong>Date:</strong> {{ formatDate(invoice.issued_at) }}</p>
          <p><strong>Status:</strong> <span class="badge">PAID</span></p>
        </div>
      </div>

      <!-- Transaction Details -->
      <div class="invoice-details">
        <table>
          <thead>
            <tr>
              <th>Description</th>
              <th style="text-align: center;">Coins</th>
              <th style="text-align: right;">Amount</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>
                <strong>{{ packageName }}</strong>
                <br>
                <small>Base Coins: {{ invoice.coins }}</small>
                <br v-if="invoice.bonus_coins > 0">
                <small v-if="invoice.bonus_coins > 0">Bonus Coins: {{ invoice.bonus_coins }}</small>
              </td>
              <td style="text-align: center;">
                {{ invoice.coins + invoice.bonus_coins }}
              </td>
              <td style="text-align: right;">
                {{ currencySymbol }}{{ formatAmount(invoice.amount) }} {{ currency }}
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Total Section -->
        <div class="total-section">
          <table>
            <tr>
              <td>Subtotal:</td>
              <td style="text-align: right;">{{ currencySymbol }}{{ formatAmount(subtotal) }}</td>
            </tr>
            <tr v-if="taxAmount > 0">
              <td>Tax{{ isIndia ? ` (GST ${gstRate}%)` : '' }}:</td>
              <td style="text-align: right;">{{ currencySymbol }}{{ formatAmount(taxAmount) }}</td>
            </tr>
            <tr class="total-row">
              <td>Total ({{ currency }}):</td>
              <td style="text-align: right;">{{ currencySymbol }}{{ formatAmount(invoice.amount) }}</td>
            </tr>
          </table>
        </div>
      </div>

      <!-- Payment Information -->
      <div class="invoice-details">
        <h3 style="margin-bottom: 15px; color: #4F46E5;">Payment Information</h3>
        <table style="width: 100%;">
          <tr>
            <td style="width: 50%; padding: 8px;"><strong>Payment Method:</strong></td>
            <td style="padding: 8px;">Razorpay</td>
          </tr>
          <tr>
            <td style="padding: 8px;"><strong>Order ID:</strong></td>
            <td style="padding: 8px;">{{ invoice.order?.razorpay_order_id }}</td>
          </tr>
          <tr>
            <td style="padding: 8px;"><strong>Payment ID:</strong></td>
            <td style="padding: 8px;">{{ invoice.order?.razorpay_payment_id }}</td>
          </tr>
          <tr>
            <td style="padding: 8px;"><strong>Transaction Date:</strong></td>
            <td style="padding: 8px;">{{ formatDateTime(invoice.order?.paid_at) }}</td>
          </tr>
        </table>
      </div>

      <!-- Footer -->
      <div class="footer">
        <p><strong>Thank you for your purchase!</strong></p>
        <p>This is a computer-generated invoice and does not require a signature.</p>
        <p>For any queries, please contact support@namate24.com</p>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

export default {
  name: 'InvoiceViewer',
  props: {
    invoiceId: {
      type: [String, Number],
      required: true
    }
  },
  setup(props) {
    const invoice = ref(null);
    const loading = ref(true);
    const error = ref(null);

    const currency = computed(() => invoice.value?.currency || 'INR');
    const currencySymbol = computed(() => currency.value === 'USD' ? '$' : '₹');
    const isIndia = computed(() => {
      const pricing = invoice.value?.order?.meta?.pricing || {};
      return pricing.is_india ?? (currency.value === 'INR');
    });

    const packageName = computed(() => {
      return invoice.value?.order?.meta?.package_name || 'Coin Package';
    });

    const subtotal = computed(() => {
      const pricing = invoice.value?.order?.meta?.pricing || {};
      return parseFloat(pricing.subtotal || invoice.value?.amount || 0);
    });

    const taxAmount = computed(() => {
      const pricing = invoice.value?.order?.meta?.pricing || {};
      return parseFloat(pricing.tax_amount || 0);
    });

    const gstRate = computed(() => {
      const pricing = invoice.value?.order?.meta?.pricing || {};
      const rate = pricing.gst_rate || 0;
      return (rate * 100).toFixed(0);
    });

    const fetchInvoice = async () => {
      try {
        loading.value = true;
        const { data } = await axios.get(`/api/wallet/invoice/${props.invoiceId}`);
        invoice.value = data.invoice || data;
      } catch (err) {
        console.error('Failed to fetch invoice:', err);
        error.value = 'Failed to load invoice. Please try again.';
      } finally {
        loading.value = false;
      }
    };

    const formatDate = (date) => {
      if (!date) return '';
      return new Date(date).toLocaleDateString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric'
      });
    };

    const formatDateTime = (date) => {
      if (!date) return '';
      return new Date(date).toLocaleString('en-IN', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
      });
    };

    const formatAmount = (amount) => {
      return parseFloat(amount || 0).toFixed(2);
    };

    const downloadPDF = () => {
      const element = document.getElementById('invoice-content');
      if (!element) return;

      // Dynamically load html2pdf if not already loaded
      if (typeof html2pdf === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js';
        script.onload = () => generatePDF(element);
        document.head.appendChild(script);
      } else {
        generatePDF(element);
      }
    };

    const generatePDF = (element) => {
      const opt = {
        margin: [5, 5, 5, 5],
        filename: `Invoice-${invoice.value?.invoice_number || 'download'}.pdf`,
        image: { type: 'jpeg', quality: 0.95 },
        html2canvas: { 
          scale: 1.5, 
          useCORS: true,
          letterRendering: true,
          scrollY: 0,
          scrollX: 0
        },
        jsPDF: { 
          unit: 'mm', 
          format: 'a4', 
          orientation: 'portrait',
          compress: true
        },
        pagebreak: { mode: 'avoid-all' }
      };
      html2pdf().set(opt).from(element).save();
    };

    const printInvoice = () => {
      window.print();
    };

    onMounted(() => {
      fetchInvoice();
    });

    return {
      invoice,
      loading,
      error,
      currency,
      currencySymbol,
      isIndia,
      packageName,
      subtotal,
      taxAmount,
      gstRate,
      formatDate,
      formatDateTime,
      formatAmount,
      downloadPDF,
      printInvoice
    };
  }
};
</script>

<style scoped>
.invoice-wrapper {
  max-width: 900px;
  margin: 0 auto;
}

.action-buttons button {
  padding: 10px 24px;
  border: none;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-download {
  background: #4F46E5;
  color: white;
}

.btn-download:hover {
  background: #4338CA;
}

.btn-print {
  background: #10B981;
  color: white;
}

.btn-print:hover {
  background: #059669;
}

.invoice-container {
  background: #fff;
  padding: 20px 30px;
  border: 1px solid #ddd;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  font-family: 'Arial', sans-serif;
  color: #333;
  line-height: 1.4;
  font-size: 13px;
}

.header {
  text-align: center;
  margin-bottom: 15px;
  border-bottom: 3px solid #4F46E5;
  padding-bottom: 12px;
}

.header h1 {
  color: #4F46E5;
  font-size: 28px;
  margin-bottom: 3px;
}

.header p {
  color: #666;
  font-size: 13px;
}

.company-details {
  margin-top: 15px;
  color: #666;
  font-size: 13px;
  line-height: 1.8;
}

.top-details-row {
  display: flex;
  gap: 15px;
  margin-bottom: 15px;
}

.company-details-section {
  flex: 1;
  background: #f9f9f9;
  padding: 10px;
  border-radius: 6px;
  text-align: left;
  color: #666;
  font-size: 11px;
  line-height: 1.5;
}

.company-details-section h3 {
  color: #4F46E5;
  font-size: 12px;
  margin-bottom: 5px;
}

.bank-details {
  flex: 1;
  background: #f9f9f9;
  padding: 10px;
  border-radius: 6px;
  text-align: left;
  font-size: 11px;
  line-height: 1.5;
}

.bank-details h3 {
  color: #4F46E5;
  font-size: 12px;
  margin-bottom: 5px;
}

.invoice-info {
  display: flex;
  justify-content: space-between;
  margin-bottom: 15px;
}

.invoice-info h3 {
  color: #4F46E5;
  font-size: 14px;
  margin-bottom: 8px;
}

.invoice-info p {
  margin: 3px 0;
  font-size: 12px;
}

.invoice-details {
  background: #f9f9f9;
  padding: 12px;
  border-radius: 8px;
  margin-bottom: 15px;
}

.invoice-details h3 {
  margin-bottom: 10px;
  color: #4F46E5;
  font-size: 14px;
}

.invoice-details table {
  width: 100%;
  border-collapse: collapse;
}

.invoice-details th {
  text-align: left;
  padding: 8px 10px;
  background: #4F46E5;
  color: white;
  font-weight: 600;
  font-size: 12px;
}

.invoice-details td {
  padding: 8px 10px;
  border-bottom: 1px solid #ddd;
  font-size: 12px;
}

.total-section {
  text-align: right;
  margin-top: 10px;
}

.total-section table {
  margin-left: auto;
  width: 280px;
}

.total-section td {
  padding: 5px 8px;
  font-size: 12px;
}

.total-section .total-row {
  font-weight: bold;
  font-size: 15px;
  background: #4F46E5;
  color: white;
}

.footer {
  margin-top: 20px;
  text-align: center;
  padding-top: 15px;
  border-top: 1px solid #ddd;
  color: #666;
  font-size: 11px;
  line-height: 1.4;
}

.footer p {
  margin: 3px 0;
}

.badge {
  display: inline-block;
  padding: 3px 10px;
  border-radius: 10px;
  font-size: 11px;
  font-weight: 600;
  background: #10B981;
  color: white;
}

@media print {
  body {
    margin: 0;
    padding: 0;
  }
  
  .action-buttons {
    display: none !important;
  }
  
  .invoice-wrapper {
    max-width: 100%;
    margin: 0;
  }
  
  .invoice-container {
    box-shadow: none;
    border: none;
    padding: 8px 15px;
    page-break-after: avoid;
    page-break-inside: avoid;
    font-size: 11px;
    line-height: 1.3;
  }
  
  .header {
    margin-bottom: 10px;
    padding-bottom: 8px;
  }
  
  .header h1 {
    font-size: 24px;
    margin-bottom: 2px;
  }
  
  .header p {
    font-size: 11px;
  }
  
  .top-details-row {
    margin-bottom: 10px;
    gap: 10px;
  }
  
  .company-details-section,
  .bank-details {
    padding: 8px;
    font-size: 10px;
    line-height: 1.4;
  }
  
  .company-details-section h3,
  .bank-details h3 {
    font-size: 11px;
    margin-bottom: 4px;
  }
  
  .invoice-info {
    margin-bottom: 10px;
  }
  
  .invoice-info h3 {
    font-size: 12px;
    margin-bottom: 5px;
  }
  
  .invoice-info p {
    font-size: 11px;
    margin: 2px 0;
  }
  
  .invoice-details {
    padding: 8px;
    margin-bottom: 10px;
  }
  
  .invoice-details h3 {
    font-size: 12px;
    margin-bottom: 6px;
  }
  
  .invoice-details th {
    padding: 6px 8px;
    font-size: 11px;
  }
  
  .invoice-details td {
    padding: 6px 8px;
    font-size: 10px;
  }
  
  .total-section {
    margin-top: 8px;
  }
  
  .total-section td {
    padding: 4px 6px;
    font-size: 11px;
  }
  
  .total-section .total-row {
    font-size: 13px;
  }
  
  .footer {
    margin-top: 12px;
    padding-top: 8px;
    font-size: 10px;
    line-height: 1.3;
  }
  
  .footer p {
    margin: 2px 0;
  }
  
  .badge {
    padding: 2px 8px;
    font-size: 10px;
  }
  
  .header, .top-details-row, .invoice-info, 
  .invoice-details, .footer {
    page-break-inside: avoid;
  }
  
  @page {
    size: A4;
    margin: 8mm;
  }
}
</style>
