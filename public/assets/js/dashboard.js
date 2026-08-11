/* ==========================================================================
   PRODUCTION-READY ACCOUNTING & FINANCE ERP DASHBOARD INTERACTION JS
   ========================================================================== */

document.addEventListener('DOMContentLoaded', function () {
    // 1. SIDEBAR COLLAPSE & SUBMENU ACCORDION TOGGLE
    // ==========================================================================
    const appLayoutWrapper = document.getElementById('appLayoutWrapper');
    const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');

    if (sidebarToggleBtn && appLayoutWrapper) {
        sidebarToggleBtn.addEventListener('click', function () {
            appLayoutWrapper.classList.toggle('sidebar-collapsed');
        });
    }

    // Submenu Toggle
    const submenuToggles = document.querySelectorAll('.submenu-toggle');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            const parentItem = this.closest('.menu-item');
            
            // Close other open submenus
            document.querySelectorAll('.menu-item.open').forEach(item => {
                if (item !== parentItem) {
                    item.classList.remove('open');
                }
            });

            parentItem.classList.toggle('open');
        });
    });

    // 2. SALES VS PURCHASE INTERACTIVE CHART (Monthly / Quarterly / Yearly)
    // ==========================================================================
    const salesPurchaseCtx = document.getElementById('salesPurchaseChart');
    let salesPurchaseChart;

    const chartDatasets = {
        monthly: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            sales: [95000, 110000, 125000, 98000, 140000, 115000, 130000, 105000, 120000, 135000, 125000, 150000],
            purchases: [65000, 72000, 85000, 70000, 92000, 80000, 88000, 75000, 82000, 90000, 82000, 98000]
        },
        quarterly: {
            labels: ['Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)'],
            sales: [330000, 353000, 355000, 410000],
            purchases: [222000, 242000, 245000, 270000]
        },
        yearly: {
            labels: ['2023', '2024', '2025', '2026 (YTD)'],
            sales: [980000, 1150000, 1380000, 1448000],
            purchases: [680000, 780000, 920000, 979000]
        }
    };

    if (salesPurchaseCtx) {
        const ctx = salesPurchaseCtx.getContext('2d');

        salesPurchaseChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartDatasets.monthly.labels,
                datasets: [
                    {
                        label: 'Sales (₹)',
                        data: chartDatasets.monthly.sales,
                        borderColor: '#2563EB',
                        backgroundColor: 'rgba(37, 99, 235, 0.08)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#2563EB',
                        pointHoverRadius: 6
                    },
                    {
                        label: 'Purchases (₹)',
                        data: chartDatasets.monthly.purchases,
                        borderColor: '#D97706',
                        backgroundColor: 'rgba(217, 119, 6, 0.04)',
                        borderWidth: 2.5,
                        borderDash: [4, 4],
                        fill: true,
                        tension: 0.35,
                        pointBackgroundColor: '#FFFFFF',
                        pointBorderColor: '#D97706',
                        pointHoverRadius: 6
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        align: 'end',
                        labels: {
                            font: { family: 'Outfit', size: 12, weight: 'bold' },
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        backgroundColor: '#0F172A',
                        padding: 12,
                        cornerRadius: 10,
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ₹' + context.parsed.y.toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Outfit', size: 11 } }
                    },
                    y: {
                        grid: { color: '#E2E8F0' },
                        ticks: {
                            font: { family: 'Outfit', size: 11 },
                            callback: function(value) {
                                return '₹' + (value / 1000) + 'k';
                            }
                        }
                    }
                }
            }
        });
    }

    // Sales vs Purchase Filter Buttons
    const btnMonthly = document.getElementById('btnFilterMonthly');
    const btnQuarterly = document.getElementById('btnFilterQuarterly');
    const btnYearly = document.getElementById('btnFilterYearly');

    function updateChartFilter(period, activeBtn) {
        if (!salesPurchaseChart) return;
        [btnMonthly, btnQuarterly, btnYearly].forEach(btn => btn?.classList.remove('active'));
        activeBtn?.classList.add('active');

        salesPurchaseChart.data.labels = chartDatasets[period].labels;
        salesPurchaseChart.data.datasets[0].data = chartDatasets[period].sales;
        salesPurchaseChart.data.datasets[1].data = chartDatasets[period].purchases;
        salesPurchaseChart.update();
    }

    btnMonthly?.addEventListener('click', () => updateChartFilter('monthly', btnMonthly));
    btnQuarterly?.addEventListener('click', () => updateChartFilter('quarterly', btnQuarterly));
    btnYearly?.addEventListener('click', () => updateChartFilter('yearly', btnYearly));

    // 3. INCOME VS EXPENSE DONUT CHART WIDGET
    // ==========================================================================
    const donutCtx = document.getElementById('incomeExpenseDonutChart');
    if (donutCtx) {
        new Chart(donutCtx.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: ['Total Income', 'Total Expense', 'Net Profit'],
                datasets: [{
                    data: [1680000, 1250000, 430000],
                    backgroundColor: ['#16A34A', '#DC2626', '#2563EB'],
                    borderWidth: 0,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '76%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ₹' + context.parsed.toLocaleString('en-IN');
                            }
                        }
                    }
                }
            }
        });
    }

    // 4. GST MONTHLY TREND LINE CHART
    // ==========================================================================
    const gstCtx = document.getElementById('gstTrendChart');
    if (gstCtx) {
        new Chart(gstCtx.getContext('2d'), {
            type: 'line',
            data: {
                labels: ['Oct', 'Nov', 'Dec', 'Jan', 'Feb'],
                datasets: [{
                    data: [65000, 72000, 78000, 80000, 82400],
                    borderColor: '#7C3AED',
                    borderWidth: 2,
                    fill: false,
                    tension: 0.3,
                    pointRadius: 3,
                    pointBackgroundColor: '#7C3AED'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { display: false }
                }
            }
        });
    }

    // 5. EXPENSE BREAKDOWN BAR CHART WIDGET
    // ==========================================================================
    const expBreakdownCtx = document.getElementById('expenseBreakdownChart');
    if (expBreakdownCtx) {
        new Chart(expBreakdownCtx.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Salary', 'Rent', 'Electricity', 'Transport', 'Office Exp', 'Purchase', 'Other'],
                datasets: [{
                    label: 'Expense Amount (₹)',
                    data: [350000, 120000, 45000, 30000, 50000, 580000, 75000],
                    backgroundColor: ['#2563EB', '#D97706', '#0284C7', '#7C3AED', '#16A34A', '#DC2626', '#64748B'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Expense: ₹' + context.parsed.y.toLocaleString('en-IN');
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: {
                        grid: { color: '#E2E8F0' },
                        ticks: {
                            font: { size: 10 },
                            callback: function(v) { return '₹' + (v / 1000) + 'k'; }
                        }
                    }
                }
            }
        });
    }

    // 6. RECENT TRANSACTIONS TABLE SEARCH FILTER
    // ==========================================================================
    const txSearchInput = document.getElementById('txTableSearch');
    const recentTxTable = document.getElementById('recentTxTable');

    if (txSearchInput && recentTxTable) {
        txSearchInput.addEventListener('keyup', function () {
            const filter = this.value.toLowerCase();
            const rows = recentTxTable.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
});
