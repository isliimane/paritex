@component('mail::message')
# Demande d'échange de produit

Votre demande de retour pour la commande #{{ $request->order_id }} a été approuvée.

**Raison du retour**:  
{{ $request->reason }}

**Solution proposée**:  
Nous vous proposons un échange du produit.

@component('mail::button', ['url' => route('exchange.instructions')])
Voir les instructions d'échange
@endcomponent

Merci,  
{{ config('app.name') }}
@endcomponent