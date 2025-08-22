<div class="header__bell" x-data="bell()" x-init="init()" :class="{'has-unread': unread > 0}">
  <button type="button"
    class="header__bell-button"
    aria-label="通知"
    aria-haspopup="menu"
    :aria-expanded="open"
    @click="toggle()">
    <i class="fas fa-bell"></i>
    <span x-show="unread > 0" x-text="unread" class="header__bell-badge"></span>
  </button>

  <div x-show="open"
    x-transition:enter="dd-enter"
    x-transition:enter-start="dd-enter-start"
    x-transition:enter-end="dd-enter-end"
    x-transition:leave="dd-leave"
    x-transition:leave-start="dd-leave-start"
    x-transition:leave-end="dd-leave-end"
    @click.outside="open=false"
    @keydown.escape.window="open=false"
    class="header__bell-dropdown"
    role="menu"
    style="display:none">
    <div class="header__bell-head">
      <span class="header__bell-title">通知</span>
      <button type="button" class="header__bell-markall" @click="markAllRead()">すべて既読</button>
    </div>

    <ul class="header__bell-list">
      <template x-for="n in items" :key="n.id">
        <li :class="{'is-unread': !n.read_at}" class="header__bell-item" role="none">
          <a :href="n.url" role="menuitem"
            @click.prevent="markOneRead(n.id).then(()=>{ window.location = n.url })">
            <div class="header__bell-item-title">
              <span class="dot" aria-hidden="true"></span>
              <span class="txt" x-text="n.title"></span>
            </div>
            <div class="header__bell-item-msg" x-text="n.message"></div>
            <div class="header__bell-item-time" x-text="n.created_at"></div>
          </a>
        </li>
      </template>
      <li x-show="items.length===0" class="header__bell-empty">新しい通知はありません</li>
    </ul>
  </div>
</div>


<script>
  const INDEX_URL = @json(route('notifications.index'));
  const READ_URL = @json(route('notifications.read'));

  function bell() {
    return {
      open: false,
      items: [],
      unread: 0,
      toggle() {
        this.open = !this.open;
        if (this.open) this.fetchList();
      },
      fetchList() {
        fetch(INDEX_URL, {
            credentials: 'same-origin'
          })
          .then(r => r.json())
          .then(d => {
            this.items = d.latest;
            this.unread = d.unread_count;
          });
      },
      markAllRead() {
        fetch(READ_URL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({})
        }).then(() => {
          this.items = this.items.map(i => ({
            ...i,
            read_at: (new Date()).toISOString()
          }));
          this.unread = 0;
        });
      },
      markOneRead(id) {
        return fetch(READ_URL, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          },
          body: JSON.stringify({
            id
          })
        }).then(() => {
          this.items = this.items.map(i => i.id === id ? ({
            ...i,
            read_at: (new Date()).toISOString()
          }) : i);
          this.unread = Math.max(0, this.unread - 1);
        });
      },
      init() {
        this.fetchList();
        setInterval(() => {
          if (!this.open) this.fetchList()
        }, 30000);
      }
    }
  }
</script>