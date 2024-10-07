<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
      <a href="/dash-home" class="app-brand-link">
        <img src="https://upload.wikimedia.org/wikipedia/commons/3/39/Department_of_Labor_and_Employment_%28DOLE%29.svg" class="app-brand-logo w-px-30 h-auto me-2 " alt="logo" />
            <span class="app-brand-text menu-text fw-bold">OPCR
              <br />
              <span class="fs-tiny fw-medium"></span>
            </span>
      </a>

      <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
        <i class="bx bx-chevron-left bx-sm align-middle"></i>
      </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Page -->
        <li class="menu-item {{ request()->is('admin_dashboard') ? 'active' : '' }}">
          <a href="/dash-home" class="menu-link">
            <i class="menu-icon tf-icons bx bx-home-circle"></i>
            <div class="text-truncate" data-i18n="Page 1">Dashboard</div>
          </a>
        </li>
        @if(auth()->user()->can('view-organizational-outcome') || auth()->user()->can('view-indicator') || auth()->user()->can('view-entries'))
          <li class="menu-item">
              <div style="margin-left: 5%; margin-top: 5%; color: #b4b0c4;">Reports</div>
          </li>
       @endif

        @can('view-organizational-outcome')
        <li class="menu-item {{ request()->is('outcome') ? 'active' : '' }}">
          <a href="/outcome" class="menu-link" @if( Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'Admin') title="Permission: manage_organizational_outcome" data-toggle="tooltip" data-placement="right" @endif>
            <i class='menu-icon tf-icons bx bx-archive-in'></i>
            <div class="text-truncate" data-i18n="Page 2">Organizational Outcome</div>
          </a>
        </li>
        @endcan

        @can('view-indicator')
        <li class="menu-item {{ request()->is('indicator') ? 'active' : '' }}">
          <a href="/indicator" class="menu-link"@if( Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'Admin') title="Permission: manage_indicator" data-toggle="tooltip" data-placement="right" @endif>
            <i class='menu-icon tf-icons bx bx-plus-circle'></i>
            <div class="text-truncate" data-i18n="Page 2">Indicator</div>
          </a>
        </li>
        @endcan

        @can('view-entries')
        <li class="menu-item {{ request()->is('entries') ? 'active' : '' }}">
          <a href="/entries" class="menu-link"@if( Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'Admin') title="Permission: manage_entries" data-toggle="tooltip" data-placement="right" @endif>
            <i class='menu-icon tf-icons bx bx-file'></i>
            <div class="text-truncate" data-i18n="Page 2">Entries</div>
            @can('manage_pending_entries')
            <span class="badge {{ $entriesCount == 0 ? 'bg-info' : 'bg-danger'}}  badge-notifications p-1 fs-8">{{$entriesCount}}</span>
            @endcan
          </a>
        </li>
        @endcan

        @can('generate-reports')
        <li class="menu-item {{ request()->is('generate') ? 'active' : '' }}">
          <a href="/generate" class="menu-link" @if( Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'Admin') title="Permission: generate-reports" data-toggle="tooltip" data-placement="right" @endif>
            <i class='menu-icon tf-icons bx bxs-file-export'></i>
            <div class="text-truncate" data-i18n="Page 2">Generate Report</div>
          </a>
        </li>
        @endcan

        @if(auth()->user()->can('manage-user-management') || auth()->user()->can('manage-roles') || auth()->user()->can('view-history') || auth()->user()->can('view-permissions'))
        <li class="menu-item">
          <div style="margin-left: 5%; margin-top: 5%; color: #b4b0c4;">User Management</div>
        </li>
        @endif

        @can('manage-roles')
        <li class="menu-item {{ request()->is('roles') ? 'active' : '' }}">
          <a href="/roles" class="menu-link" @if( Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'Admin') title="Permission: manage-roles" data-toggle="tooltip" data-placement="right" @endif>
            {{-- <i class='menu-icon bx bx-purchase-tag-alt'></i> --}}
            <i class='menu-icon bx bx-shield-plus'></i>
            {{-- <i class='menu-icon tf-icons bx bx-group'></i> --}}
            <div class="text-truncate" data-i18n="Page 2">Role</div>
          </a>
        </li>
        @endcan

        @can('view-permissions')
        <li class="menu-item" {{ request()->is('permissions') ? 'active' : '' }}>
          <a href="/permissions" class="menu-link" @if( Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'Admin') title="Permission: manage_permissions" data-toggle="tooltip" data-placement="right" @endif>
            <i class='menu-icon tf-icons bx bx-key'></i>
            <div class="text-truncate" data-i18n="Page 2">Permission</div>
          </a>
        </li>
        @endcan

        @can('manage-user-management')
        <li class="menu-item {{ request()->is('user') ? 'active' : '' }}">
          <a href="/user" class="menu-link" @if( Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'Admin') title="Permission: manage_users" data-toggle="tooltip" data-placement="right" @endif>
            <i class='menu-icon tf-icons bx bx-group'></i>
            <div class="text-truncate" data-i18n="Page 2">Users</div>
          </a>
        </li>
        @endcan

        @can('view-history')
        <li class="menu-item" {{ request()->is('login_in') ? 'active' : '' }}>
          <a href="/login_in" class="menu-link" @if( Auth::user()->role->name === 'IT' ||  Auth::user()->role->name === 'Admin') title="Permission: manage_history" data-toggle="tooltip" data-placement="right" @endif>
            <i class='menu-icon tf-icons bx bx-history' ></i>
            <div class="text-truncate" data-i18n="Page 2">History</div>
          </a>
        </li>
        @endcan

      </ul>
  </aside>
