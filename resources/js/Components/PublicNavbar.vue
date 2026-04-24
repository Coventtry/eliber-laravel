<template>
    <nav class="navbar navbar-expand-md navbar-eliber navbar-dark">
        <div class="container-fluid">
            <Link :href="route('landing')" class="navbar-brand font-weight-bold d-flex align-items-center">
                <img src="/img/logo.png" alt="E-liber" height="32" class="mr-2">
            </Link>

            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#publicNav" aria-controls="publicNav" aria-expanded="false" aria-label="Menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="publicNav">
                <ul class="navbar-nav mr-auto">
                    <li class="nav-item">
                        <Link :href="route('faqs')" class="nav-link">
                            <i class="bi bi-question-circle mr-1"></i>FAQs
                        </Link>
                    </li>
                    <li class="nav-item">
                        <Link :href="route('acerca')" class="nav-link">
                            <i class="bi bi-info-circle mr-1"></i>Acerca
                        </Link>
                    </li>
                </ul>
                <Link :href="route('login')" class="btn btn-outline-light btn-sm ml-md-2">
                    <i class="bi bi-box-arrow-in-right mr-1"></i>Ingresar
                </Link>
            </div>
        </div>
    </nav>
</template>
