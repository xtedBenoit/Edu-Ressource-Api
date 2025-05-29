<p>Bonjour {{ $user->name }},</p>

<p>Voici votre code de réinitialisation de mot de passe :</p>

<h2 style="color: #333;">{{ $code }}</h2>

<p>Ce code est valide pendant 15 minutes.</p>

<p>— L'équipe {{ config('app.name') }}</p>
