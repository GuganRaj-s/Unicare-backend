 <!-- App Sidebar -->
 <div class="modal fade panelbox panelbox-left" id="sidebarPanel" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-body p-0">
                    <!-- profile box -->
                    <div class="profileBox pt-2 pb-2">
                        <a href="{{ url('dashboard/profile')}}" >
                            <div class="image-wrapper">
                                <ion-icon class="icon" name="person-circle-outline"></ion-icon>
                            </div>
                        </a>
                        <div class="in">
                            <strong>{{ ucfirst(Auth::user()->name) }}</strong>
                            <div class="text-muted">{{ Auth::user()->user_identifier }}</div>
                        </div>
                        <a href="#" class="btn btn-link btn-icon sidebar-close" data-bs-dismiss="modal">
                            <ion-icon name="close-outline"></ion-icon>
                        </a>
                    </div>
                    <!-- * profile box -->

                    <!-- menu -->
                    <!--div class="listview-title mt-1">Master</div-->
                    <ul class="listview flush transparent no-line image-listview" id="DisplayDynamicMenu">

                        <!--li>
                            <a href="{{url('dashboard')}}" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="pie-chart-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Dashboard
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('categories')}}" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="apps-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Category
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('wallets')}}" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="wallet-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Wallet Type
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('operators')}}" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="wifi-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Operator
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('user')}}" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="people-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    User Management
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('usermargin')}}" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="code-slash-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    User Margin
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('payment')}}" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="cash-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Payment Transfer
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('transaction')}}" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="stats-chart-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Transaction
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('rchpermission')}}" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="radio-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Recharge Permission
                                </div>
                            </a>
                        </li>

                        <li>
                            <a href="{{url('apigateway')}}" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="repeat-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    API Gateways
                                </div>
                            </a>
                        </li>
                        <li>
                            <a href="{{url('smsgateway')}}" class="item">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="chatbox-ellipses-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    SMS Gateway
                                </div>
                            </a>
                        </li-->

                        
                    </ul>
                    <ul class="listview flush transparent no-line image-listview">
                        <li>
                            <a href="{{ route('logout') }}" class="item" onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                <div class="icon-box bg-primary">
                                    <ion-icon name="power-outline"></ion-icon>
                                </div>
                                <div class="in">
                                    Logout
                                </div>
                            </a>
                        </li>
                    </ul>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                    </form>
                    <!-- * others -->

                </div>
            </div>
        </div>
    </div>
    <!-- * App Sidebar -->
