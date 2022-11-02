from django.contrib import admin
from django.urls import path
from . import views

urlpatterns = [
    path('', views.joomla_view),
    path('login', views.login, name='login'),
    path('register', views.register, name='register'),
    path('logout', views.logout, name='logout'),
    path('todolist', views.todolist, name='todolist'),
    path('home', views.PostList.as_view(), name='home'),
    path('<slug:slug>/', views.PostDetail.as_view(), name='post_detail'),
    
]