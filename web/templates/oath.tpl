{include file='header.tpl'}
<div class="row">
<div class="columns span16">
  {if $oath}
  <hgroup>
  <h1>問おう、あなたが私のマスターか？</h1>
  </hgroup>
  <form action="/oath" method="post">
  <button type="submit" class="btn btn-primary">はい。そうです。</button>
  </from>
  {/if}
</div>
</div>

{include file='footer.tpl'}