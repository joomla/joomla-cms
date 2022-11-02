from django.shortcuts import render, redirect
from django.contrib import messages
from django.contrib.auth.models import User, auth

from django.views import generic
from .models import Post

class PostList(generic.ListView):
    queryset = Post.objects.filter(status=1).order_by('-created_on')
    template_name = 'index.html'

class PostDetail(generic.DetailView):
    model = Post
    template_name = 'post_details.html'


# Create your views here.
def joomla_view(request):
	# render function takes argument - request
	# and return HTML as response
	return render(request, "home.html")


def todolist(request):
	return render(request, "todolist.html")

def login(request):
    if request.method == 'POST':
        username = request.POST['Username']
        password = request.POST['Password']

        user = auth.authenticate(username=username, password=password)

        if user is not None:
            auth.login(request, user)
            return redirect('/')
        else:
            messages.info(request, 'Invalid Credentials')
            return redirect('login')
    else:
        return render(request, 'login.html')


def register(request):
    if request.method == 'POST':
        username = request.POST['username']
        email = request.POST['email']
        password = request.POST['password']
        if User.objects.filter(username=username).exists():
            messages.info(request, 'Username Taken')
            return redirect('register')
        elif User.objects.filter(email=email).exists():
            messages.info(request, 'Email is already Taken')
            return redirect('register')
        else:
            user = User.objects.create_user(username=username, password=password, email=email)
            user.save()
            return redirect('login')
    else:
        return render(request, 'register.html')

def logout(request):
    auth.logout(request)
    return redirect('/')