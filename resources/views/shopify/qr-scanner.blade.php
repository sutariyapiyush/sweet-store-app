<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('QR Code Scanner') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="text-center">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Scan Order or Invoice QR Code</h3>

                        <!-- Camera Scanner -->
                        <div id="scanner-container" class="mb-6">
                            <video id="qr-video" class="mx-auto border rounded-lg" width="400" height="300" style="display: none;"></video>
                            <div id="scanner-placeholder" class="mx-auto border-2 border-dashed border-gray-300 rounded-lg p-8 w-96 h-72 flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-camera text-gray-400 text-4xl mb-4"></i>
                                    <p class="text-gray-500 mb-4">Click to start camera scanner</p>
                                    <button onclick="startScanner()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Start Scanner
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Manual Input -->
                        <div class="mb-6">
                            <h4 class="text-md font-medium text-gray-700 mb-2">Or Enter QR Code Data Manually</h4>
                            <div class="flex space-x-2">
                                <input type="text" id="manual-qr-input" placeholder="Paste QR code data here..."
                                       class="flex-1 border border-gray-300 rounded-md px-3 py-2">
                                <button onclick="processManualInput()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                                    Process
                                </button>
                            </div>
                        </div>

                        <!-- Scanner Controls -->
                        <div id="scanner-controls" class="mb-6" style="display: none;">
                            <button onclick="stopScanner()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded mr-2">
                                Stop Scanner
                            </button>
                            <button onclick="switchCamera()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Switch Camera
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Scan Results -->
            <div id="scan-results" class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg" style="display: none;">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Scan Results</h3>
                    <div id="results-content"></div>
                </div>
            </div>

            <!-- Action Modal -->
            <div id="actionModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <div class="mt-3">
                        <h3 class="text-lg font-medium text-gray-900 mb-4" id="modal-title">Execute Action</h3>
                        <div id="modal-content"></div>
                        <div class="mt-6 flex justify-end space-x-2">
                            <button onclick="closeActionModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                                Cancel
                            </button>
                            <button onclick="executeSelectedAction()" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                Execute
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/qr-scanner@1.4.2/qr-scanner.umd.min.js"></script>
    <script>
        let qrScanner = null;
        let currentQrData = null;
        let selectedAction = null;

        function startScanner() {
            const video = document.getElementById('qr-video');
            const placeholder = document.getElementById('scanner-placeholder');
            const controls = document.getElementById('scanner-controls');

            video.style.display = 'block';
            placeholder.style.display = 'none';
            controls.style.display = 'block';

            qrScanner = new QrScanner(
                video,
                result => {
                    console.log('QR Code detected:', result.data);
                    processQrCode(result.data);
                },
                {
                    onDecodeError: error => {
                        // Handle decode errors silently
                    },
                    highlightScanRegion: true,
                    highlightCodeOutline: true,
                }
            );

            qrScanner.start().catch(error => {
                console.error('Failed to start scanner:', error);
                alert('Failed to start camera scanner. Please check camera permissions.');
                stopScanner();
            });
        }

        function stopScanner() {
            if (qrScanner) {
                qrScanner.stop();
                qrScanner.destroy();
                qrScanner = null;
            }

            const video = document.getElementById('qr-video');
            const placeholder = document.getElementById('scanner-placeholder');
            const controls = document.getElementById('scanner-controls');

            video.style.display = 'none';
            placeholder.style.display = 'flex';
            controls.style.display = 'none';
        }

        function switchCamera() {
            if (qrScanner) {
                qrScanner.setCamera('environment').catch(() => {
                    qrScanner.setCamera('user').catch(error => {
                        console.error('Failed to switch camera:', error);
                    });
                });
            }
        }

        function processManualInput() {
            const input = document.getElementById('manual-qr-input');
            const qrData = input.value.trim();

            if (qrData) {
                processQrCode(qrData);
                input.value = '';
            } else {
                alert('Please enter QR code data');
            }
        }

        function processQrCode(qrData) {
            currentQrData = qrData;

            fetch('{{ route("shopify.process-scan") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ qr_data: qrData })
            })
            .then(response => response.json())
            .then(data => {
                displayScanResults(data);
            })
            .catch(error => {
                console.error('Error processing QR code:', error);
                alert('Failed to process QR code');
            });
        }

        function displayScanResults(data) {
            const resultsDiv = document.getElementById('scan-results');
            const contentDiv = document.getElementById('results-content');

            if (data.success) {
                let html = '';

                if (data.type === 'order') {
                    html = `
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                            <h4 class="text-lg font-medium text-green-800 mb-2">Order Found</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><strong>Order Number:</strong> ${data.order.order_number}</div>
                                <div><strong>Customer:</strong> ${data.order.customer_name || 'N/A'}</div>
                                <div><strong>Total:</strong> ${data.order.currency} ${data.order.total_price}</div>
                                <div><strong>Status:</strong> <span class="capitalize">${data.order.internal_status}</span></div>
                            </div>
                        </div>
                    `;
                } else if (data.type === 'invoice') {
                    html = `
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                            <h4 class="text-lg font-medium text-blue-800 mb-2">Invoice Found</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div><strong>Invoice Number:</strong> ${data.invoice.invoice_number}</div>
                                <div><strong>Customer:</strong> ${data.invoice.customer_name}</div>
                                <div><strong>Total:</strong> ${data.invoice.currency} ${data.invoice.total_amount}</div>
                                <div><strong>Status:</strong> <span class="capitalize">${data.invoice.status}</span></div>
                            </div>
                        </div>
                    `;
                }

                // Add available actions
                if (data.actions && data.actions.length > 0) {
                    html += '<div class="mt-4"><h5 class="font-medium text-gray-700 mb-2">Available Actions:</h5>';
                    html += '<div class="grid grid-cols-1 gap-2">';

                    data.actions.forEach(action => {
                        html += `
                            <button onclick="showActionModal('${action.action}', '${action.label}', '${action.description}')"
                                    class="text-left p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                <div class="font-medium">${action.label}</div>
                                <div class="text-sm text-gray-500">${action.description}</div>
                            </button>
                        `;
                    });

                    html += '</div></div>';
                }

                contentDiv.innerHTML = html;
            } else {
                contentDiv.innerHTML = `
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <h4 class="text-lg font-medium text-red-800 mb-2">Error</h4>
                        <p class="text-red-700">${data.error || 'Failed to process QR code'}</p>
                    </div>
                `;
            }

            resultsDiv.style.display = 'block';
        }

        function showActionModal(action, label, description) {
            selectedAction = action;
            document.getElementById('modal-title').textContent = label;
            document.getElementById('modal-content').innerHTML = `
                <p class="text-gray-700 mb-4">${description}</p>
                <p class="text-sm text-gray-500">Are you sure you want to execute this action?</p>
            `;
            document.getElementById('actionModal').classList.remove('hidden');
        }

        function closeActionModal() {
            document.getElementById('actionModal').classList.add('hidden');
            selectedAction = null;
        }

        function executeSelectedAction() {
            if (!selectedAction || !currentQrData) {
                alert('No action selected');
                return;
            }

            fetch('{{ route("shopify.execute-action") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    qr_data: currentQrData,
                    action: selectedAction,
                    params: {}
                })
            })
            .then(response => response.json())
            .then(data => {
                closeActionModal();

                if (data.success) {
                    alert('✅ Action executed successfully: ' + data.message);
                    // Refresh the scan results
                    processQrCode(currentQrData);
                } else {
                    alert('❌ Action failed: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error executing action:', error);
                alert('❌ Failed to execute action');
                closeActionModal();
            });
        }

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => {
            if (qrScanner) {
                qrScanner.destroy();
            }
        });
    </script>
</x-app-layout>
