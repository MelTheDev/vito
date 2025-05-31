import { Head, useForm, usePage } from '@inertiajs/react';
import { Server } from '@/types/server';
import Container from '@/components/container';
import HeaderContainer from '@/components/header-container';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import ServerLayout from '@/layouts/server/layout';
import { BookOpenIcon, LoaderCircleIcon } from 'lucide-react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import ServerStatus from '@/pages/servers/components/status';
import DateTime from '@/components/date-time';
import CopyableBadge from '@/components/copyable-badge';
import { Input } from '@/components/ui/input';
import React, { useState } from 'react';

export default function Databases() {
  const page = usePage<{
    server: Server;
  }>();

  const [editMode, setEditMode] = useState<string | undefined>();

  const form = useForm<{
    name: string;
    ip: string;
    port: string;
    local_ip?: string;
  }>({
    name: page.props.server.name,
    ip: page.props.server.ip,
    port: page.props.server.port.toString(),
    local_ip: page.props.server.local_ip,
  });

  const submit = () => {
    form.patch(route('server-settings.update', { server: page.props.server.id }), {
      onSuccess: () => {
        setEditMode(undefined);
      },
    });
  };

  const handleEnterKey = (e: React.KeyboardEvent<HTMLInputElement>) => {
    if (e.key === 'Enter') {
      e.preventDefault();
      submit();
    }
  };

  return (
    <ServerLayout>
      <Head title={`Settings - ${page.props.server.name}`} />

      <Container className="max-w-5xl">
        <HeaderContainer>
          <Heading title="Settings" description="Here you can manage your server's settings" />
          <div className="flex items-center gap-2">
            <a href="https://vitodeploy.com/docs/servers/settings" target="_blank">
              <Button variant="outline">
                <BookOpenIcon />
                <span className="hidden lg:block">Docs</span>
              </Button>
            </a>
          </div>
        </HeaderContainer>

        <Card>
          <CardHeader className="flex-row items-center justify-between gap-2">
            <div className="space-y-2">
              <CardTitle>Server details</CardTitle>
              <CardDescription>Update server details</CardDescription>
            </div>
            <div className="flex items-center gap-2">
              {form.isDirty && (
                <Button onClick={submit}>
                  {form.processing && <LoaderCircleIcon className="animate-spin" />}
                  Save changes
                </Button>
              )}
              {(editMode || form.isDirty) && (
                <Button
                  variant="outline"
                  onClick={() => {
                    setEditMode(undefined);
                    form.reset();
                  }}
                >
                  Cancel
                </Button>
              )}
            </div>
          </CardHeader>
          <CardContent>
            <div className="flex items-center justify-between p-4">
              <span>ID</span>
              <span className="text-muted-foreground">{page.props.server.id}</span>
            </div>
            <Separator />
            <div className="flex items-center justify-between p-4">
              <span>Name</span>
              {editMode === 'name' ? (
                <Input
                  id="name"
                  className="h-6 max-w-48"
                  value={form.data.name}
                  onChange={(e) => form.setData('name', e.target.value)}
                  onKeyDown={handleEnterKey}
                  autoFocus
                />
              ) : (
                <span className="text-muted-foreground cursor-pointer underline" onClick={() => setEditMode('name')}>
                  {form.data.name}
                </span>
              )}
            </div>
            <Separator />
            <div className="flex items-center justify-between p-4">
              <span>Status</span>
              <ServerStatus server={page.props.server} />
            </div>
            <Separator />
            <div className="flex items-center justify-between p-4">
              <span>IP</span>
              {editMode === 'ip' ? (
                <Input
                  id="ip"
                  className="h-6 max-w-48"
                  value={form.data.ip}
                  onChange={(e) => form.setData('ip', e.target.value)}
                  onKeyDown={handleEnterKey}
                  autoFocus
                />
              ) : (
                <span className="text-muted-foreground cursor-pointer underline" onClick={() => setEditMode('ip')}>
                  {form.data.ip}
                </span>
              )}
            </div>
            <Separator />
            <div className="flex items-center justify-between p-4">
              <span>SSH Port</span>
              {editMode === 'port' ? (
                <Input
                  id="port"
                  className="h-6 max-w-48"
                  value={form.data.port}
                  onChange={(e) => form.setData('port', e.target.value)}
                  onKeyDown={handleEnterKey}
                  autoFocus
                />
              ) : (
                <span className="text-muted-foreground cursor-pointer underline" onClick={() => setEditMode('port')}>
                  {form.data.port}
                </span>
              )}
            </div>
            <Separator />
            <div className="flex items-center justify-between p-4">
              <span>Local IP</span>
              {editMode === 'local_ip' ? (
                <Input
                  id="local_ip"
                  className="h-6 max-w-48"
                  value={form.data.local_ip ?? ''}
                  onChange={(e) => form.setData('local_ip', e.target.value)}
                  onKeyDown={handleEnterKey}
                  autoFocus
                />
              ) : (
                <span className="text-muted-foreground cursor-pointer underline" onClick={() => setEditMode('local_ip')}>
                  {form.data.local_ip ? form.data.local_ip : 'Click to set'}
                </span>
              )}
            </div>
            <Separator />
            <div className="flex items-center justify-between p-4">
              <span>Created at</span>
              <span className="text-muted-foreground">
                <DateTime date={page.props.server.created_at} />
              </span>
            </div>
            <Separator />
            <div className="flex items-center justify-between p-4">
              <span>Last update check</span>
              <span className="text-muted-foreground">
                {page.props.server.last_update_check ? <DateTime date={page.props.server.last_update_check} /> : '-'}
              </span>
            </div>
            <Separator />
            <div className="flex items-center justify-between p-4">
              <span>Available updates</span>
              <span className="text-muted-foreground">
                <span className="text-muted-foreground">{page.props.server.available_updates ?? '-'}</span>
              </span>
            </div>
            <Separator />
            <div className="flex items-center justify-between p-4">
              <span>Provider</span>
              <span className="text-muted-foreground">
                <span className="text-muted-foreground">{page.props.server.provider}</span>
              </span>
            </div>
            <Separator />
            <div className="flex items-center justify-between p-4">
              <span>Public key</span>
              <CopyableBadge text={page.props.server.public_key} />
            </div>
          </CardContent>
        </Card>
      </Container>
    </ServerLayout>
  );
}
