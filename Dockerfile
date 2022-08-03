FROM python:3.8.9-alpine
RUN apk add --no-cache make bash
ENTRYPOINT ["bash"]
