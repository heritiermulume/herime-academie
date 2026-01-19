<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Facture - {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #2c3e50;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 40px;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 3px solid #003366;
            position: relative;
        }
        .logo-container {
            margin-bottom: 20px;
        }
        .logo-container img {
            max-width: 200px;
            height: auto;
        }
        .header h1 {
            color: #003366;
            font-size: 32px;
            margin-bottom: 10px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .header p {
            color: #6c757d;
            font-size: 14px;
        }
        .invoice-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        .invoice-info-section {
            flex: 1;
            min-width: 250px;
            margin-bottom: 20px;
        }
        .invoice-info-section h3 {
            color: #003366;
            font-size: 16px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            padding-bottom: 8px;
            border-bottom: 2px solid #ffcc33;
        }
        .info-item {
            margin-bottom: 8px;
            font-size: 14px;
        }
        .info-item strong {
            color: #003366;
            display: inline-block;
            min-width: 140px;
            font-weight: 600;
        }
        .info-item span {
            color: #2c3e50;
        }
        .status-badge {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            color: #ffffff;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table thead {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
        }
        .items-table th {
            padding: 14px 12px;
            text-align: left;
            font-weight: 600;
            color: #ffffff;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .items-table th.text-center {
            text-align: center;
        }
        .items-table th.text-right {
            text-align: right;
        }
        .items-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #e9ecef;
            font-size: 14px;
        }
        .items-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .course-title {
            font-weight: 600;
            color: #003366;
            margin-bottom: 4px;
        }
        .course-meta {
            font-size: 12px;
            color: #6c757d;
        }
        .totals-section {
            margin-top: 30px;
            margin-left: auto;
            width: 300px;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            font-size: 14px;
        }
        .total-row.total {
            border-top: 3px solid #003366;
            margin-top: 10px;
            padding-top: 15px;
            font-size: 20px;
            font-weight: 700;
            color: #003366;
        }
        .total-label {
            color: #2c3e50;
            font-weight: 500;
        }
        .total-value {
            color: #003366;
            font-weight: 600;
        }
        .footer {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
            text-align: center;
            color: #6c757d;
            font-size: 13px;
            background-color: #f8f9fa;
            padding: 30px 20px;
            border-radius: 8px;
        }
        .footer p {
            margin-bottom: 8px;
        }
        .footer strong {
            color: #003366;
            font-size: 16px;
        }
        .payment-info {
            background: linear-gradient(135deg, rgba(0, 51, 102, 0.05) 0%, rgba(0, 64, 128, 0.05) 100%);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #003366;
        }
        .payment-info h3 {
            color: #003366;
            font-size: 16px;
            margin-bottom: 15px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .message {
            background: linear-gradient(135deg, rgba(255, 204, 51, 0.1) 0%, rgba(255, 204, 51, 0.05) 100%);
            border-left: 4px solid #ffcc33;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 4px;
        }
        .message p {
            color: #856404;
            font-size: 14px;
            margin: 0;
        }
        .message strong {
            color: #003366;
        }
        @media print {
            body {
                background-color: #ffffff;
            }
            .container {
                padding: 20px;
            }
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
            .invoice-info {
                flex-direction: column;
            }
            .totals-section {
                width: 100%;
            }
            .logo-container img {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($logoUrl))
            <div class="logo-container">
                <img src="{{ $logoUrl }}" alt="Herime Académie Logo" />
            </div>
            @endif
            <h1>FACTURE</h1>
            <p>Preuve de paiement</p>
        </div>

        <div class="invoice-info">
            <div class="invoice-info-section">
                <h3>Informations de la facture</h3>
                <div class="info-item">
                    <strong>Numéro de facture :</strong>
                    <span>{{ $order->order_number }}</span>
                </div>
                <div class="info-item">
                    <strong>Date d'émission :</strong>
                    <span>{{ $order->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="info-item">
                    <strong>Date de paiement :</strong>
                    <span>{{ $order->paid_at ? $order->paid_at->format('d/m/Y à H:i') : $order->created_at->format('d/m/Y à H:i') }}</span>
                </div>
                <div class="info-item">
                    <strong>Statut :</strong>
                    <span class="status-badge">Payé</span>
                </div>
            </div>

            <div class="invoice-info-section">
                <h3>Facturé à</h3>
                <div class="info-item">
                    <strong>Nom :</strong>
                    <span>{{ $user->name }}</span>
                </div>
                <div class="info-item">
                    <strong>Email :</strong>
                    <span>{{ $user->email }}</span>
                </div>
                @if($user->phone)
                <div class="info-item">
                    <strong>Téléphone :</strong>
                    <span>{{ $user->phone }}</span>
                </div>
                @endif
            </div>
        </div>

        @if($payment)
        <div class="payment-info">
            <h3>Informations de paiement</h3>
            <div class="info-item">
                <strong>Mode de paiement :</strong>
                <span>{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? $order->payment_method ?? 'Non spécifié')) }}</span>
            </div>
            @if($order->payment_provider)
            <div class="info-item">
                <strong>Fournisseur :</strong>
                <span>{{ strtoupper($order->payment_provider) }}</span>
            </div>
            @endif
            @if($order->payment_reference)
            <div class="info-item">
                <strong>Référence :</strong>
                <span>{{ $order->payment_reference }}</span>
            </div>
            @endif
            @if($order->payment_id)
            <div class="info-item">
                <strong>ID de transaction :</strong>
                <span>{{ $order->payment_id }}</span>
            </div>
            @endif
        </div>
        @endif

        <div class="message">
            <p><strong>Merci pour votre achat !</strong> Votre paiement a été traité avec succès. Cette facture fait foi de votre transaction.</p>
        </div>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="text-center">Quantité</th>
                    <th class="text-right">Prix unitaire</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($orderItems as $item)
                @php
                    $course = $item->course;
                    $itemPrice = $item->sale_price ?? $item->price;
                    $itemTotal = $item->total ?? $itemPrice;
                @endphp
                <tr>
                    <td>
                        <div class="course-title">
                            @if($course)
                                {{ $course->title }}
                            @else
                                Contenu supprimé
                            @endif
                        </div>
                        @if($course && $course->instructor)
                        <div class="course-meta">Formateur : {{ $course->instructor->name }}</div>
                        @endif
                    </td>
                    <td class="text-center">1</td>
                    <td class="text-right">{{ number_format((float)$itemPrice, 2, ',', ' ') }} {{ $order->currency }}</td>
                    <td class="text-right"><strong>{{ number_format((float)$itemTotal, 2, ',', ' ') }} {{ $order->currency }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals-section">
            @if($order->subtotal)
            <div class="total-row">
                <span class="total-label">Sous-total :</span>
                <span class="total-value">{{ number_format((float)$order->subtotal, 2, ',', ' ') }} {{ $order->currency }}</span>
            </div>
            @endif
            
            @if($order->discount && $order->discount > 0)
            <div class="total-row">
                <span class="total-label">Réduction :</span>
                <span class="total-value" style="color: #28a745;">- {{ number_format((float)$order->discount, 2, ',', ' ') }} {{ $order->currency }}</span>
            </div>
            @endif

            @if($order->coupon)
            <div class="total-row">
                <span class="total-label">Code promo :</span>
                <span class="total-value" style="color: #ffcc33;">{{ $order->coupon->code }}</span>
            </div>
            @endif

            @if($order->tax && $order->tax > 0)
            <div class="total-row">
                <span class="total-label">Taxes :</span>
                <span class="total-value">{{ number_format((float)$order->tax, 2, ',', ' ') }} {{ $order->currency }}</span>
            </div>
            @endif

            <div class="total-row total">
                <span class="total-label">Total TTC :</span>
                <span class="total-value">{{ number_format((float)($order->total_amount ?? $order->total ?? 0), 2, ',', ' ') }} {{ $order->currency }}</span>
            </div>
        </div>

        <div class="footer">
            <p><strong>Herime Académie</strong></p>
            <p>Merci d'avoir choisi nos formations en ligne !</p>
            <p>Pour toute question concernant cette facture, n'hésitez pas à nous contacter.</p>
            <p style="margin-top: 15px; font-size: 12px; color: #6c757d;">
                Cette facture a été générée automatiquement le {{ now()->format('d/m/Y à H:i') }}
            </p>
        </div>
    </div>
</body>
</html>
