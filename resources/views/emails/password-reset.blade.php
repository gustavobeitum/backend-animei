<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Redefinição de Senha</title>
    <style>
      h1 {
        color: #00adfe;
        margin-top: 30px;
        font-size: 2.1em;
      }
      body {
        line-height: 1.6;
        color: #ffffff;
        background-color: #1E1F1F;
        font-family:  "Arial", sans-serif
      }
      .container {
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #384358;
        border-radius: 5px;
        background: #242F49;
      }
      p{
        color: #ffffff;
        font-size: 1.2em
      }
      .code {
        font-size: 3em;
        font-weight: bold;
        color: #00adfe;
      }
      .aviso{
        font-size: 0.9em;
      }
      strong{
        color: #00adfe;
      }
      .rodape{
        margin-top: 40px;
        font-size: 0.9em;
        text-align: center;
      }
    </style>
  </head>
  <body>
    <div class="container">
        <h1>Código de Redefinição de Senha</h1>
      <p>Olá,</p>
      <p>Utilize esse código abaixo para redefinir sua senha:</p>
      <p class="code">{{ $code }}</p>
      <p class="aviso">(Esse código é válido por 15 minutos a partir do momento em que foi solicitado)</p>
      <p>Se você não solicitou essa redefinição, por favor ignore este e-mail.</p>
      <p>Atenciosamente,   ANIMEI</p><hr>
      <p class="rodape">Precisa de ajuda? <strong>Entre em contato com nossa equipe de assistência</strong>.<br>Quer nos dar sua opnião? Diga o que acha em nosso <strong>site de comentários</strong>.</p>
    </div>
  </body>
</html>
