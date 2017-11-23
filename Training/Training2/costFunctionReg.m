function [J, grad] = costFunctionReg(theta, X, y, lambda)
%COSTFUNCTIONREG Compute cost and gradient for logistic regression with regularization
%   J = COSTFUNCTIONREG(theta, X, y, lambda) computes the cost of using
%   theta as the parameter for regularized logistic regression and the
%   gradient of the cost w.r.t. to the parameters. 

% Initialize some useful values
m = length(y); % number of training examples
n = length(theta); % number of features

% You need to return the following variables correctly 
J = 0;
grad = zeros(size(theta));

% ====================== YOUR CODE HERE ======================
% Instructions: Compute the cost of a particular choice of theta.
%               You should set J to the cost.
%               Compute the partial derivatives and set grad to the partial
%               derivatives of the cost w.r.t. each parameter in theta

p = 0;
for i = 1:m,
  hx = sigmoid(X(i, :) * theta);
  p = p + (-y(i)*log(hx) - (1-y(i))*log(1-hx));
end;
p = p/m;

r = 0
for j=2:n,
  r = r + theta(j)^2;
end;
r = r*lambda/(2*m);

J = r + p;

for j=1:n,
  sum = 0;
  for i=1:m,
    hx = sigmoid(X(i, :) * theta);
    hx = hx - y(i, :);
    hx = hx * X(i, j);
    sum = sum + hx;
  end;
  sum = sum/m;

  if j ~= 1,
    sum = sum + (lambda * theta(j) )/m;
  end;
  grad(j) = sum;
end;

disp(J);



% =============================================================

end
