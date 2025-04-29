<!DOCTYPE html>
<html>
<head>
    <title>{{ $subject }}</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #2d3748;">Bonjour {{ $userName }},</h2>
        
        <p>Vous avez reçu une réponse concernant votre réclamation :</p>
        
        <div style="background-color: #f7fafc; padding: 1rem; border-left: 4px solid #4299e1; margin: 1rem 0;">
            <h3 style="margin-top: 0;">{{ $claimSubject }}</h3>
            <p><strong>Statut :</strong> {{ ucfirst(str_replace('_', ' ', $status)) }}</p>
            <p>{{ $adminResponse }}</p>
        </div>
        
        <a href="{{ $claimLink }}" 
           style="display: inline-block; background-color: #4299e1; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 0.25rem; margin-top: 1rem;">
            Voir ma réclamation
        </a>
        
        <p style="margin-top: 2rem;">Cordialement,<br>L'équipe {{ config('app.name') }}</p>
    </div>
</body>
</html>
